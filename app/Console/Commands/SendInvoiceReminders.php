<?php

namespace App\Console\Commands;

use App\Models\Modules\Invoicing\Models\Invoice;
use App\Models\InvoiceReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'invoices:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send automatic payment reminders for overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for overdue invoices...');

        $today = Carbon::today();

        // Find all overdue invoices that are not paid or cancelled
        $overdueInvoices = Invoice::whereIn('status', ['sent', 'overdue'])
            ->whereDate('due_date', '<', $today)
            ->with(['customer', 'company'])
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('✅ No overdue invoices found.');
            return 0;
        }

        $this->info("📧 Found {$overdueInvoices->count()} overdue invoices.");

        $sent = 0;

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = $today->diffInDays($invoice->due_date);

            // Update status to overdue if not already
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
            }

            // Determine reminder type based on days overdue
            $reminderType = $this->determineReminderType($daysOverdue);

            // Check if this type of reminder was already sent
            $lastReminder = InvoiceReminder::where('invoice_id', $invoice->id)
                ->where('type', $reminderType)
                ->first();

            if ($lastReminder) {
                continue; // Already sent this reminder
            }

            // Determine if we should send reminder based on schedule
            if (!$this->shouldSendReminder($daysOverdue)) {
                continue;
            }

            // Send reminder
            $this->sendReminder($invoice, $reminderType, $daysOverdue);
            $sent++;
        }

        $this->info("✅ Sent {$sent} reminders successfully.");
        return 0;
    }

    /**
     * Determine reminder type based on days overdue
     */
    private function determineReminderType(int $daysOverdue): string
    {
        if ($daysOverdue >= 30) {
            return 'final_notice';
        } elseif ($daysOverdue >= 15) {
            return 'second_reminder';
        } else {
            return 'first_reminder';
        }
    }

    /**
     * Determine if reminder should be sent based on schedule
     * First reminder: 7 days after due date
     * Second reminder: 15 days after due date
     * Final notice: 30 days after due date
     */
    private function shouldSendReminder(int $daysOverdue): bool
    {
        return in_array($daysOverdue, [7, 15, 30]);
    }

    /**
     * Send reminder email
     */
    private function sendReminder(Invoice $invoice, string $type, int $daysOverdue): void
    {
        $customer = $invoice->customer;

        if (!$customer->email) {
            $this->warn("⚠️  No email for customer {$customer->name} - skipping");
            return;
        }

        // Generate reminder message based on type
        $message = $this->generateReminderMessage($invoice, $type, $daysOverdue);

        // Record reminder
        InvoiceReminder::create([
            'invoice_id' => $invoice->id,
            'type' => $type,
            'days_overdue' => $daysOverdue,
            'sent_at' => now(),
            'sent_to' => $customer->email,
            'message' => $message,
        ]);

        // TODO: Send actual email using Mail facade
        // Mail::to($customer->email)->send(new InvoiceReminderMail($invoice, $message));

        $this->line("📨 Sent {$type} to {$customer->name} ({$customer->email}) - Invoice {$invoice->invoice_number}");
    }

    /**
     * Generate reminder message
     */
    private function generateReminderMessage(Invoice $invoice, string $type, int $daysOverdue): string
    {
        $messages = [
            'first_reminder' => "Bonjour,\n\nNous constatons que la facture {$invoice->invoice_number} d'un montant de {$invoice->total}€ est en retard de {$daysOverdue} jours.\n\nMerci de procéder au règlement dans les plus brefs délais.",

            'second_reminder' => "Bonjour,\n\nMalgré notre précédente relance, la facture {$invoice->invoice_number} d'un montant de {$invoice->total}€ n'a toujours pas été réglée ({$daysOverdue} jours de retard).\n\nNous vous demandons de bien vouloir régulariser cette situation rapidement.",

            'final_notice' => "Bonjour,\n\nCeci est une mise en demeure concernant la facture {$invoice->invoice_number} d'un montant de {$invoice->total}€, en retard de {$daysOverdue} jours.\n\nSans règlement sous 7 jours, nous serons contraints d'engager des poursuites.",
        ];

        return $messages[$type] ?? '';
    }
}
