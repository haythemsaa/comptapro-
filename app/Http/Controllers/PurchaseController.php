<?php

namespace App\Http\Controllers;

use App\Models\Modules\Purchases\Models\{Supplier, PurchaseInvoice, PurchaseInvoiceLine};
use App\Models\Modules\Products\Models\Product;
use App\Models\Modules\Accounting\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchase invoices
     */
    public function index(Request $request): Response
    {
        $companyId = session('current_company_id');

        if (!$companyId) {
            return Inertia::render('Purchases/Index', [
                'invoices' => [],
                'filters' => $request->only(['search', 'status', 'type'])
            ]);
        }

        $query = PurchaseInvoice::where('company_id', $companyId)
            ->with(['supplier', 'lines']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('supplier_invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
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

        return Inertia::render('Purchases/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status', 'type'])
        ]);
    }

    /**
     * Show the form for creating a new purchase invoice
     */
    public function create(): Response
    {
        $companyId = session('current_company_id');

        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'supplier_number', 'email', 'payment_term']);

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'unit_price', 'vat_rate', 'sku']);

        $accounts = Account::where('company_id', $companyId)
            ->whereIn('type', ['expense', 'asset'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return Inertia::render('Purchases/Create', [
            'suppliers' => $suppliers,
            'products' => $products,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Store a newly created purchase invoice
     */
    public function store(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_invoice_number' => 'nullable|string|max:255',
            'type' => 'required|in:purchase,credit_note',
            'status' => 'required|in:draft,received,approved,paid,cancelled',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'nullable|exists:products,id',
            'lines.*.account_id' => 'nullable|exists:accounts,id',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit' => 'required|string',
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

            // Create purchase invoice
            $invoice = PurchaseInvoice::create([
                'company_id' => $companyId,
                'supplier_id' => $validated['supplier_id'],
                'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
                'type' => $validated['type'],
                'status' => $validated['status'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total' => $subtotal + $taxAmount,
                'paid_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create invoice lines
            foreach ($validated['lines'] as $index => $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineSubtotal * ($line['vat_rate'] / 100);

                PurchaseInvoiceLine::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $line['product_id'] ?? null,
                    'account_id' => $line['account_id'] ?? null,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                    'unit_price' => $line['unit_price'],
                    'vat_rate' => $line['vat_rate'],
                    'subtotal' => $lineSubtotal,
                    'tax_amount' => $lineTax,
                    'total' => $lineSubtotal + $lineTax,
                    'line_order' => $index,
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.show', $invoice->id)
                ->with('success', 'Facture d\'achat créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified purchase invoice
     */
    public function show(PurchaseInvoice $purchase): Response
    {
        $purchase->load(['supplier', 'lines.product', 'lines.account', 'createdBy', 'approvedBy']);

        return Inertia::render('Purchases/Show', [
            'invoice' => $purchase,
        ]);
    }

    /**
     * Show the form for editing the specified purchase invoice
     */
    public function edit(PurchaseInvoice $purchase): Response
    {
        $companyId = session('current_company_id');

        // Only allow editing draft invoices
        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.show', $purchase->id)
                ->with('error', 'Seules les factures en brouillon peuvent être modifiées.');
        }

        $purchase->load(['supplier', 'lines']);

        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'supplier_number', 'email']);

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'unit_price', 'vat_rate', 'sku']);

        $accounts = Account::where('company_id', $companyId)
            ->whereIn('type', ['expense', 'asset'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return Inertia::render('Purchases/Edit', [
            'invoice' => $purchase,
            'suppliers' => $suppliers,
            'products' => $products,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Update the specified purchase invoice
     */
    public function update(Request $request, PurchaseInvoice $purchase)
    {
        // Only allow updating draft invoices
        if ($purchase->status !== 'draft') {
            return back()->withErrors(['error' => 'Seules les factures en brouillon peuvent être modifiées.']);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_invoice_number' => 'nullable|string|max:255',
            'type' => 'required|in:purchase,credit_note',
            'status' => 'required|in:draft,received,approved,paid,cancelled',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'nullable|exists:products,id',
            'lines.*.account_id' => 'nullable|exists:accounts,id',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit' => 'required|string',
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

            // Update purchase invoice
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
                'type' => $validated['type'],
                'status' => $validated['status'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'notes' => $validated['notes'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
            ]);

            // Delete existing lines
            $purchase->lines()->delete();

            // Create new lines
            foreach ($validated['lines'] as $index => $line) {
                $lineSubtotal = $line['quantity'] * $line['unit_price'];
                $lineTax = $lineSubtotal * ($line['vat_rate'] / 100);

                PurchaseInvoiceLine::create([
                    'purchase_invoice_id' => $purchase->id,
                    'product_id' => $line['product_id'] ?? null,
                    'account_id' => $line['account_id'] ?? null,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                    'unit_price' => $line['unit_price'],
                    'vat_rate' => $line['vat_rate'],
                    'subtotal' => $lineSubtotal,
                    'tax_amount' => $lineTax,
                    'total' => $lineSubtotal + $lineTax,
                    'line_order' => $index,
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Facture d\'achat mise à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark invoice as received
     */
    public function markReceived(PurchaseInvoice $purchase)
    {
        $purchase->update([
            'status' => 'received',
            'received_at' => now(),
        ]);

        return back()->with('success', 'Facture marquée comme reçue.');
    }

    /**
     * Approve the invoice
     */
    public function approve(PurchaseInvoice $purchase)
    {
        $purchase->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Facture approuvée.');
    }

    /**
     * Record a payment for the invoice
     */
    public function markPaid(Request $request, PurchaseInvoice $purchase)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . ($purchase->total - $purchase->paid_amount),
            'payment_date' => 'required|date',
        ]);

        $newPaidAmount = $purchase->paid_amount + $validated['amount'];
        $status = $newPaidAmount >= $purchase->total ? 'paid' : 'approved';

        $purchase->update([
            'paid_amount' => $newPaidAmount,
            'status' => $status,
        ]);

        return back()->with('success', 'Paiement enregistré avec succès.');
    }

    /**
     * Remove the specified purchase invoice
     */
    public function destroy(PurchaseInvoice $purchase)
    {
        if ($purchase->status === 'paid') {
            return back()->withErrors(['error' => 'Impossible de supprimer une facture payée.']);
        }

        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Facture d\'achat supprimée.');
    }

    /**
     * Upload and process invoice with OCR
     */
    public function uploadWithOcr(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        $companyId = session('current_company_id');

        DB::beginTransaction();
        try {
            // Store the file
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('purchases/invoices/' . $companyId, $filename, 'private');

            // Create draft invoice
            $invoice = PurchaseInvoice::create([
                'company_id' => $companyId,
                'supplier_id' => $validated['supplier_id'],
                'type' => 'purchase',
                'status' => 'draft',
                'invoice_date' => now(),
                'subtotal' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'ocr_processed' => false,
                'created_by' => auth()->id(),
            ]);

            // TODO: Process OCR here
            // This would integrate with services like:
            // - Tesseract OCR
            // - Google Cloud Vision API
            // - AWS Textract
            // - Azure Computer Vision

            DB::commit();

            return redirect()->route('purchases.edit', $invoice->id)
                ->with('success', 'Fichier téléchargé. Veuillez vérifier et compléter les informations.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors du téléchargement: ' . $e->getMessage()]);
        }
    }
}
