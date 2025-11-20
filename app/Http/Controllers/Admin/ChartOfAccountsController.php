<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Tunisia\TunisiaChartOfAccount;
use App\Models\Belgium\BelgiumChartOfAccount;
use Illuminate\Http\Request;

/**
 * Contrôleur de gestion du plan comptable
 * Compatible Tunisia (PCN) et Belgium (PCMN)
 */
class ChartOfAccountsController extends Controller
{
    /**
     * Liste le plan comptable
     */
    public function index(Request $request)
    {
        $country = $request->get('country', 'TN');
        $search = $request->get('search');
        $accountClass = $request->get('class');
        $type = $request->get('type');

        if ($country === 'TN') {
            $query = TunisiaChartOfAccount::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('account_number', 'like', "%{$search}%")
                      ->orWhere('account_name', 'like', "%{$search}%");
                });
            }

            if ($accountClass) {
                $query->where('class', $accountClass);
            }

            if ($type) {
                $query->where('type', $type);
            }

            $accounts = $query->orderBy('account_number')->paginate(50);

            $classes = TunisiaChartOfAccount::distinct('class')
                ->orderBy('class')
                ->pluck('class')
                ->filter();

        } elseif ($country === 'BE') {
            $query = BelgiumChartOfAccount::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('account_number', 'like', "%{$search}%")
                      ->orWhere('account_name', 'like', "%{$search}%")
                      ->orWhere('account_name_nl', 'like', "%{$search}%");
                });
            }

            if ($accountClass) {
                $query->where('class', $accountClass);
            }

            if ($type) {
                $query->where('type', $type);
            }

            $accounts = $query->orderBy('account_number')->paginate(50);

            $classes = BelgiumChartOfAccount::distinct('class')
                ->orderBy('class')
                ->pluck('class')
                ->filter();

        } else {
            $accounts = collect();
            $classes = collect();
        }

        $types = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        return view('admin.accounting.chart-of-accounts.index', compact('accounts', 'country', 'classes', 'types'));
    }

    /**
     * Afficher les détails d'un compte
     */
    public function show(Request $request, string $accountNumber)
    {
        $country = $request->get('country', 'TN');

        if ($country === 'TN') {
            $account = TunisiaChartOfAccount::where('account_number', $accountNumber)->firstOrFail();
        } elseif ($country === 'BE') {
            $account = BelgiumChartOfAccount::where('account_number', $accountNumber)->firstOrFail();
        } else {
            abort(404);
        }

        // Récupérer les écritures sur ce compte
        $entries = \App\Models\JournalLine::where('account_number', $accountNumber)
            ->with('journalEntry.company')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculer le solde
        $totalDebit = \App\Models\JournalLine::where('account_number', $accountNumber)->sum('debit');
        $totalCredit = \App\Models\JournalLine::where('account_number', $accountNumber)->sum('credit');
        $balance = $totalDebit - $totalCredit;

        return view('admin.accounting.chart-of-accounts.show', compact('account', 'entries', 'totalDebit', 'totalCredit', 'balance', 'country'));
    }

    /**
     * Créer un compte personnalisé
     */
    public function create(Request $request)
    {
        $country = $request->get('country', 'TN');
        $types = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        if ($country === 'TN') {
            $classes = ['1', '2', '3', '4', '5', '6', '7'];
        } elseif ($country === 'BE') {
            $classes = ['0', '1', '2', '3', '4', '5', '6', '7'];
        } else {
            $classes = [];
        }

        return view('admin.accounting.chart-of-accounts.create', compact('country', 'types', 'classes'));
    }

    /**
     * Enregistrer un compte personnalisé
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required|in:TN,BE',
            'account_number' => 'required|string|max:20',
            'account_name' => 'required|string|max:255',
            'account_name_nl' => 'nullable|string|max:255',
            'account_name_en' => 'nullable|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'class' => 'required|string|max:1',
            'is_active' => 'boolean',
            'can_post' => 'boolean',
        ]);

        try {
            if ($validated['country'] === 'TN') {
                // Vérifier que le compte n'existe pas déjà
                if (TunisiaChartOfAccount::where('account_number', $validated['account_number'])->exists()) {
                    return back()->withInput()->with('error', 'Ce numéro de compte existe déjà');
                }

                TunisiaChartOfAccount::create([
                    'account_number' => $validated['account_number'],
                    'account_name' => $validated['account_name'],
                    'type' => $validated['type'],
                    'class' => $validated['class'],
                    'is_active' => $validated['is_active'] ?? true,
                    'can_post' => $validated['can_post'] ?? true,
                ]);

            } elseif ($validated['country'] === 'BE') {
                // Vérifier que le compte n'existe pas déjà
                if (BelgiumChartOfAccount::where('account_number', $validated['account_number'])->exists()) {
                    return back()->withInput()->with('error', 'Ce numéro de compte existe déjà');
                }

                BelgiumChartOfAccount::create([
                    'account_number' => $validated['account_number'],
                    'account_name' => $validated['account_name'],
                    'account_name_nl' => $validated['account_name_nl'] ?? $validated['account_name'],
                    'account_name_en' => $validated['account_name_en'] ?? $validated['account_name'],
                    'type' => $validated['type'],
                    'class' => $validated['class'],
                    'is_active' => $validated['is_active'] ?? true,
                    'can_post' => $validated['can_post'] ?? true,
                ]);
            }

            return redirect()->route('admin.accounting.chart-of-accounts.index', ['country' => $validated['country']])
                ->with('success', 'Compte créé avec succès');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Éditer un compte
     */
    public function edit(Request $request, string $accountNumber)
    {
        $country = $request->get('country', 'TN');

        if ($country === 'TN') {
            $account = TunisiaChartOfAccount::where('account_number', $accountNumber)->firstOrFail();
        } elseif ($country === 'BE') {
            $account = BelgiumChartOfAccount::where('account_number', $accountNumber)->firstOrFail();
        } else {
            abort(404);
        }

        $types = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        if ($country === 'TN') {
            $classes = ['1', '2', '3', '4', '5', '6', '7'];
        } elseif ($country === 'BE') {
            $classes = ['0', '1', '2', '3', '4', '5', '6', '7'];
        }

        return view('admin.accounting.chart-of-accounts.edit', compact('account', 'country', 'types', 'classes'));
    }

    /**
     * Mettre à jour un compte
     */
    public function update(Request $request, string $accountNumber)
    {
        $validated = $request->validate([
            'country' => 'required|in:TN,BE',
            'account_name' => 'required|string|max:255',
            'account_name_nl' => 'nullable|string|max:255',
            'account_name_en' => 'nullable|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'is_active' => 'boolean',
            'can_post' => 'boolean',
        ]);

        try {
            if ($validated['country'] === 'TN') {
                $account = TunisiaChartOfAccount::where('account_number', $accountNumber)->firstOrFail();
                $account->update([
                    'account_name' => $validated['account_name'],
                    'type' => $validated['type'],
                    'is_active' => $validated['is_active'] ?? $account->is_active,
                    'can_post' => $validated['can_post'] ?? $account->can_post,
                ]);

            } elseif ($validated['country'] === 'BE') {
                $account = BelgiumChartOfAccount::where('account_number', $accountNumber)->firstOrFail();
                $account->update([
                    'account_name' => $validated['account_name'],
                    'account_name_nl' => $validated['account_name_nl'] ?? $account->account_name_nl,
                    'account_name_en' => $validated['account_name_en'] ?? $account->account_name_en,
                    'type' => $validated['type'],
                    'is_active' => $validated['is_active'] ?? $account->is_active,
                    'can_post' => $validated['can_post'] ?? $account->can_post,
                ]);
            }

            return redirect()->route('admin.accounting.chart-of-accounts.show', [
                'accountNumber' => $accountNumber,
                'country' => $validated['country'],
            ])->with('success', 'Compte mis à jour avec succès');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Activer/Désactiver un compte
     */
    public function toggleStatus(Request $request, string $accountNumber)
    {
        $country = $request->get('country', 'TN');

        if ($country === 'TN') {
            $account = TunisiaChartOfAccount::where('account_number', $accountNumber)->firstOrFail();
        } elseif ($country === 'BE') {
            $account = BelgiumChartOfAccount::where('account_number', $accountNumber)->firstOrFail();
        } else {
            abort(404);
        }

        $account->update(['is_active' => !$account->is_active]);

        $status = $account->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Compte {$status} avec succès");
    }

    /**
     * Export du plan comptable
     */
    public function export(Request $request)
    {
        $country = $request->get('country', 'TN');

        // TODO: Implémenter l'export Excel/CSV
        return back()->with('info', 'Export à implémenter');
    }
}
