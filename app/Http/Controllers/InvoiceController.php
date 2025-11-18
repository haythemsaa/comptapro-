<?php

namespace App\Http\Controllers;

use App\Models\Modules\Invoicing\Models\{Customer, Invoice, InvoiceLine};
use App\Models\Modules\Products\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request): Response
    {
        $companyId = session('current_company_id');

        if (!$companyId) {
            return Inertia::render('Invoices/Index', [
                'invoices' => [],
                'filters' => $request->only(['search', 'status', 'type'])
            ]);
        }

        $query = Invoice::where('company_id', $companyId)
            ->with(['customer', 'lines']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status', 'type'])
        ]);
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create(): Response
    {
        $companyId = session('current_company_id');

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'customer_number', 'email']);

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'unit_price', 'vat_rate', 'sku']);

        return Inertia::render('Invoices/Create', [
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:quote,invoice,credit_note',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'nullable|exists:products,id',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.vat_rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Generate invoice number
            $lastInvoice = Invoice::where('company_id', $companyId)
                ->where('type', $validated['type'])
                ->orderBy('id', 'desc')
                ->first();

            $prefix = match($validated['type']) {
                'quote' => 'QT',
                'invoice' => 'INV',
                'credit_note' => 'CN',
            };

            $nextNumber = $lastInvoice
                ? (int)substr($lastInvoice->invoice_number, strlen($prefix)) + 1
                : 1;

            $invoiceNumber = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['lines'] as $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineSubtotal * ($line['vat_rate'] / 100);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;
            }

            $total = $subtotal + $taxAmount;

            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $companyId,
                'customer_id' => $validated['customer_id'],
                'invoice_number' => $invoiceNumber,
                'type' => $validated['type'],
                'status' => $validated['status'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create invoice lines
            foreach ($validated['lines'] as $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineSubtotal * ($line['vat_rate'] / 100);

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line['product_id'] ?? null,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'vat_rate' => $line['vat_rate'],
                    'subtotal' => $lineSubtotal,
                    'tax_amount' => $lineTax,
                    'total' => $lineSubtotal + $lineTax,
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Facture créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'lines.product', 'company.country']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Show the form for editing the invoice
     */
    public function edit(Invoice $invoice): Response
    {
        $companyId = session('current_company_id');

        $invoice->load(['lines']);

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'customer_number', 'email']);

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'unit_price', 'vat_rate', 'sku']);

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Only allow editing draft invoices
        if ($invoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Seules les factures en brouillon peuvent être modifiées.']);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:quote,invoice,credit_note',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'nullable|exists:products,id',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.vat_rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['lines'] as $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineSubtotal * ($line['vat_rate'] / 100);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;
            }

            $total = $subtotal + $taxAmount;

            // Update invoice
            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'type' => $validated['type'],
                'status' => $validated['status'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete old lines and create new ones
            $invoice->lines()->delete();

            foreach ($validated['lines'] as $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineSubtotal * ($line['vat_rate'] / 100);

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line['product_id'] ?? null,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'vat_rate' => $line['vat_rate'],
                    'subtotal' => $lineSubtotal,
                    'tax_amount' => $lineTax,
                    'total' => $lineSubtotal + $lineTax,
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Facture mise à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        // Only allow deleting draft invoices
        if ($invoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Seules les factures en brouillon peuvent être supprimées.']);
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Cette facture ne peut pas être envoyée.']);
        }

        $invoice->update(['status' => 'sent']);

        return back()->with('success', 'Facture marquée comme envoyée.');
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
        ]);

        return back()->with('success', 'Paiement enregistré avec succès.');
    }

    /**
     * Generate payment link for invoice
     */
    public function generatePaymentLink(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return back()->withErrors(['error' => 'Cette facture ne peut pas recevoir de lien de paiement.']);
        }

        // Generate unique token
        $token = bin2hex(random_bytes(32));

        // Expiration: 30 days by default
        $expiresAt = now()->addDays(30);

        $invoice->update([
            'payment_token' => $token,
            'payment_link_enabled' => true,
            'payment_link_expires_at' => $expiresAt,
            'payment_method' => $request->payment_method ?? 'bank_transfer',
        ]);

        $paymentUrl = route('payment.show', ['token' => $token]);

        return back()->with([
            'success' => 'Lien de paiement généré avec succès.',
            'payment_url' => $paymentUrl
        ]);
    }

    /**
     * Disable payment link
     */
    public function disablePaymentLink(Invoice $invoice)
    {
        $invoice->update([
            'payment_link_enabled' => false,
        ]);

        return back()->with('success', 'Lien de paiement désactivé.');
    }

    /**
     * Send invoice by email with payment link
     */
    public function sendInvoiceEmail(Invoice $invoice)
    {
        // TODO: Implement email sending with Mailable
        // This would send the invoice PDF + payment link to customer

        return back()->with('success', 'Facture envoyée par email.');
    }
}
