<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\PdfGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invoice $invoice,
        public string $reminderType,
        public int $daysOverdue
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'first_reminder' => 'Rappel : Facture {invoice_number} à échéance',
            'second_reminder' => 'Rappel urgent : Facture {invoice_number} en retard',
            'final_notice' => 'Mise en demeure : Facture {invoice_number}',
        ];

        $subject = str_replace(
            '{invoice_number}',
            $this->invoice->invoice_number,
            $subjects[$this->reminderType] ?? 'Rappel de paiement'
        );

        return new Envelope(
            from: new Address(
                $this->invoice->company->email ?? config('mail.from.address'),
                $this->invoice->company->name
            ),
            replyTo: [
                new Address(
                    $this->invoice->company->email ?? config('mail.from.address'),
                    $this->invoice->company->name
                ),
            ],
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-reminder',
            with: [
                'invoice' => $this->invoice,
                'company' => $this->invoice->company,
                'customer' => $this->invoice->customer,
                'reminderType' => $this->reminderType,
                'daysOverdue' => $this->daysOverdue,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdfGenerator = new PdfGenerator();
        $pdf = $pdfGenerator->generateInvoicePdf($this->invoice);

        $filename = str_replace(' ', '_', "Facture_{$this->invoice->invoice_number}.pdf");

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
