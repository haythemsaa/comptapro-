<?php

namespace App\Http\Controllers;

use App\Models\Modules\Purchases\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request): Response
    {
        $companyId = session('current_company_id');

        if (!$companyId) {
            return Inertia::render('Suppliers/Index', [
                'suppliers' => [],
                'filters' => $request->only(['search', 'category'])
            ]);
        }

        $query = Supplier::where('company_id', $companyId);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('supplier_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $suppliers = $query->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'category'])
        ]);
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create(): Response
    {
        return Inertia::render('Suppliers/Create');
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|size:2',
            'vat_number' => 'nullable|string|max:50',
            'payment_term' => 'required|in:immediate,15_days,30_days,45_days,60_days,90_days',
            'bank_account' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
            'contact_person' => 'nullable|string|max:255',
            'category' => 'required|in:goods,services,both',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;

        $supplier = Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur créé avec succès.');
    }

    /**
     * Display the specified supplier
     */
    public function show(Supplier $supplier): Response
    {
        $supplier->load(['purchaseInvoices' => function($query) {
            $query->orderBy('invoice_date', 'desc')->limit(10);
        }]);

        $stats = [
            'total_invoices' => $supplier->purchaseInvoices()->count(),
            'total_spent' => $supplier->purchaseInvoices()->sum('total'),
            'pending_amount' => $supplier->purchaseInvoices()
                ->whereIn('status', ['received', 'approved'])
                ->sum('total') - $supplier->purchaseInvoices()
                ->whereIn('status', ['received', 'approved'])
                ->sum('paid_amount'),
        ];

        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit(Supplier $supplier): Response
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|size:2',
            'vat_number' => 'nullable|string|max:50',
            'payment_term' => 'required|in:immediate,15_days,30_days,45_days,60_days,90_days',
            'bank_account' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
            'contact_person' => 'nullable|string|max:255',
            'category' => 'required|in:goods,services,both',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur mis à jour avec succès.');
    }

    /**
     * Remove the specified supplier
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has invoices
        if ($supplier->purchaseInvoices()->count() > 0) {
            return back()->withErrors(['error' => 'Impossible de supprimer un fournisseur avec des factures.']);
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur supprimé.');
    }
}
