<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Contrôleur de gestion des entreprises (CRUD complet)
 */
class CompanyController extends Controller
{
    /**
     * Liste toutes les entreprises
     */
    public function index(Request $request)
    {
        $query = Company::with(['users']);

        // Filtres
        if ($request->filled('country')) {
            $query->where('country_code', $request->country);
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('tax_id', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $companies = $query->paginate(20);

        // Options pour filtres
        $countries = ['TN' => 'Tunisie', 'BE' => 'Belgique'];
        $sectors = Company::distinct('sector')->pluck('sector')->filter();

        return view('admin.companies.index', compact('companies', 'countries', 'sectors'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $countries = ['TN' => 'Tunisie', 'BE' => 'Belgique', 'FR' => 'France', 'MA' => 'Maroc'];
        $sectors = ['services', 'commerce', 'industrie', 'construction', 'agriculture', 'technologie', 'autre'];

        return view('admin.companies.create', compact('countries', 'sectors'));
    }

    /**
     * Enregistrer une nouvelle entreprise
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'required|string|unique:companies,tax_id',
            'vat_number' => 'nullable|string',
            'country_code' => 'required|string|size:2',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'sector' => 'nullable|string',
            'registration_number' => 'nullable|string',
            'fiscal_year_end' => 'nullable|integer|min:1|max:12',

            // Utilisateur admin de l'entreprise
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            // Créer l'entreprise
            $company = Company::create([
                'name' => $validated['name'],
                'legal_name' => $validated['legal_name'] ?? $validated['name'],
                'tax_id' => $validated['tax_id'],
                'vat_number' => $validated['vat_number'],
                'country_code' => $validated['country_code'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'sector' => $validated['sector'],
                'registration_number' => $validated['registration_number'],
                'fiscal_year_end' => $validated['fiscal_year_end'] ?? 12,
                'is_active' => true,
            ]);

            // Créer l'utilisateur admin
            $user = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'company_id' => $company->id,
                'role' => 'admin',
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.companies.show', $company)
                ->with('success', 'Entreprise créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une entreprise
     */
    public function show(Company $company)
    {
        $company->load(['users']);

        // Statistiques de l'entreprise
        $stats = [
            'users_count' => $company->users()->count(),
            'active_users' => $company->users()->where('is_active', true)->count(),
            'created_days_ago' => $company->created_at->diffInDays(now()),
        ];

        return view('admin.companies.show', compact('company', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Company $company)
    {
        $countries = ['TN' => 'Tunisie', 'BE' => 'Belgique', 'FR' => 'France', 'MA' => 'Maroc'];
        $sectors = ['services', 'commerce', 'industrie', 'construction', 'agriculture', 'technologie', 'autre'];

        return view('admin.companies.edit', compact('company', 'countries', 'sectors'));
    }

    /**
     * Mettre à jour une entreprise
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'required|string|unique:companies,tax_id,' . $company->id,
            'vat_number' => 'nullable|string',
            'country_code' => 'required|string|size:2',
            'email' => 'required|email|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'sector' => 'nullable|string',
            'registration_number' => 'nullable|string',
            'fiscal_year_end' => 'nullable|integer|min:1|max:12',
            'is_active' => 'boolean',
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', 'Entreprise mise à jour avec succès');
    }

    /**
     * Activer/Désactiver une entreprise
     */
    public function toggleStatus(Company $company)
    {
        $company->update(['is_active' => !$company->is_active]);

        $status = $company->is_active ? 'activée' : 'désactivée';

        return back()->with('success', "Entreprise {$status} avec succès");
    }

    /**
     * Supprimer une entreprise
     */
    public function destroy(Company $company)
    {
        if ($company->users()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une entreprise avec des utilisateurs');
        }

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Entreprise supprimée avec succès');
    }
}
