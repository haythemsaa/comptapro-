<?php

namespace App\Http\Controllers;

use App\Models\Modules\Invoicing\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request): Response
    {
        $companyId = session('current_company_id');

        $customers = Customer::where('company_id', $companyId)
            ->withCount('invoices')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Show the form for creating a new customer
     */
    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:2',
            'vat_number' => 'nullable|string|max:50',
            'payment_term' => 'required|in:immediate,15_days,30_days,45_days,60_days',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Generate customer number
        $lastCustomer = Customer::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastCustomer ? (int)substr($lastCustomer->customer_number, 1) + 1 : 1;
        $validated['customer_number'] = 'C' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $validated['company_id'] = $companyId;

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer): Response
    {
        $customer->load(['invoices' => function ($query) {
            $query->orderBy('invoice_date', 'desc')->limit(10);
        }]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:2',
            'vat_number' => 'nullable|string|max:50',
            'payment_term' => 'required|in:immediate,15_days,30_days,45_days,60_days',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Client supprimé avec succès.');
    }
}
