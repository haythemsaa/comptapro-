<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Contrôleur de gestion des utilisateurs (CRUD complet)
 */
class UserController extends Controller
{
    /**
     * Liste tous les utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::with(['company']);

        // Filtres
        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(20);

        // Options pour filtres
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $roles = ['admin', 'accountant', 'user', 'viewer'];

        return view('admin.users.index', compact('users', 'companies', 'roles'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $roles = ['admin', 'accountant', 'user', 'viewer'];

        return view('admin.users.create', compact('companies', 'roles'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company_id' => 'required|exists:companies,id',
            'role' => ['required', Rule::in(['admin', 'accountant', 'user', 'viewer'])],
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'],
            'role' => $validated['role'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Utilisateur créé avec succès');
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(User $user)
    {
        $user->load(['company']);

        // Statistiques de l'utilisateur
        $stats = [
            'account_age_days' => $user->created_at->diffInDays(now()),
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais',
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $roles = ['admin', 'accountant', 'user', 'viewer'];

        return view('admin.users.edit', compact('user', 'companies', 'roles'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'company_id' => 'required|exists:companies,id',
            'role' => ['required', Rule::in(['admin', 'accountant', 'user', 'viewer'])],
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_id' => $validated['company_id'],
            'role' => $validated['role'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ];

        // Mise à jour du mot de passe uniquement si fourni
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès');
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Utilisateur {$status} avec succès");
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Mot de passe réinitialisé avec succès');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // Empêcher la suppression du dernier admin d'une entreprise
        if ($user->role === 'admin') {
            $adminCount = User::where('company_id', $user->company_id)
                ->where('role', 'admin')
                ->where('is_active', true)
                ->count();

            if ($adminCount <= 1) {
                return back()->with('error', 'Impossible de supprimer le dernier administrateur de l\'entreprise');
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès');
    }

    /**
     * Récupérer les utilisateurs par entreprise (API)
     */
    public function byCompany(Company $company)
    {
        $users = $company->users()->orderBy('name')->get(['id', 'name', 'email', 'role']);

        return response()->json($users);
    }
}
