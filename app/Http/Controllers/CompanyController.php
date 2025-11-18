<?php

namespace App\Http\Controllers;

use App\Models\Modules\Core\Models\{Company, Country};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies
     */
    public function index(): Response
    {
        $user = Auth::user();

        $companies = $user->companies()
            ->with(['country', 'users'])
            ->withCount('users')
            ->get();

        return Inertia::render('Companies/Index', [
            'companies' => $companies
        ]);
    }

    /**
     * Show the form for creating a new company
     */
    public function create(): Response
    {
        $countries = Country::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'currency', 'default_vat_rate']);

        return Inertia::render('Companies/Create', [
            'countries' => $countries
        ]);
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'state' => 'nullable|string|max:100',
            'fiscal_year_start' => 'required|integer|between:1,12',
            'subscription_plan' => 'required|in:starter,professional,enterprise',
        ]);

        $company = Company::create($validated);

        // Attach current user as admin
        $company->users()->attach(Auth::id(), [
            'role' => 'admin',
            'is_active' => true
        ]);

        // Set as current company
        session(['current_company_id' => $company->id]);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Société créée avec succès.');
    }

    /**
     * Display the specified company
     */
    public function show(Company $company): Response
    {
        $company->load(['country', 'users']);

        return Inertia::render('Companies/Show', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for editing the company
     */
    public function edit(Company $company): Response
    {
        $countries = Country::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'currency', 'default_vat_rate']);

        return Inertia::render('Companies/Edit', [
            'company' => $company,
            'countries' => $countries
        ]);
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'state' => 'nullable|string|max:100',
            'fiscal_year_start' => 'required|integer|between:1,12',
            'subscription_plan' => 'required|in:starter,professional,enterprise',
        ]);

        $company->update($validated);

        return redirect()->route('companies.show', $company)
            ->with('success', 'Société mise à jour avec succès.');
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        // Check if user has admin rights
        $pivot = $company->users()->where('user_id', Auth::id())->first();

        if (!$pivot || $pivot->pivot->role !== 'admin') {
            return back()->withErrors(['error' => 'Vous devez être administrateur pour supprimer cette société.']);
        }

        // Check if it's the last company
        if (Auth::user()->companies()->count() === 1) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas supprimer votre dernière société.']);
        }

        $company->delete();

        // Switch to another company
        $newCompany = Auth::user()->companies()->first();
        session(['current_company_id' => $newCompany->id]);

        return redirect()->route('companies.index')
            ->with('success', 'Société supprimée avec succès.');
    }

    /**
     * Manage users of the company
     */
    public function manageUsers(Company $company): Response
    {
        $company->load(['users', 'country']);

        return Inertia::render('Companies/ManageUsers', [
            'company' => $company
        ]);
    }

    /**
     * Add user to company
     */
    public function addUser(Request $request, Company $company)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,accountant,user',
        ]);

        $user = \App\Models\User::where('email', $validated['email'])->first();

        // Check if user is already in company
        if ($company->users()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'Cet utilisateur fait déjà partie de cette société.']);
        }

        $company->users()->attach($user->id, [
            'role' => $validated['role'],
            'is_active' => true
        ]);

        return back()->with('success', 'Utilisateur ajouté avec succès.');
    }

    /**
     * Update user role in company
     */
    public function updateUserRole(Request $request, Company $company, int $userId)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,accountant,user',
        ]);

        $company->users()->updateExistingPivot($userId, [
            'role' => $validated['role']
        ]);

        return back()->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Remove user from company
     */
    public function removeUser(Company $company, int $userId)
    {
        // Prevent removing the last admin
        $adminCount = $company->users()
            ->wherePivot('role', 'admin')
            ->count();

        $userRole = $company->users()
            ->where('user_id', $userId)
            ->first()
            ->pivot
            ->role;

        if ($adminCount === 1 && $userRole === 'admin') {
            return back()->withErrors(['error' => 'Vous ne pouvez pas retirer le dernier administrateur.']);
        }

        $company->users()->detach($userId);

        return back()->with('success', 'Utilisateur retiré avec succès.');
    }
}
