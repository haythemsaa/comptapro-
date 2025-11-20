<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Tunisia\TunisiaChartOfAccount;
use App\Models\Belgium\BelgiumChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Contrôleur de gestion des écritures comptables
 * Compatible Tunisia (PCN) et Belgium (PCMN)
 */
class JournalEntryController extends Controller
{
    /**
     * Liste toutes les écritures comptables
     */
    public function index(Request $request)
    {
        $query = JournalEntry::with(['company', 'lines']);

        // Filtres
        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('journal_type')) {
            $query->where('journal_type', $request->journal_type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $entries = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Options pour filtres
        $companies = Company::orderBy('name')->get(['id', 'name', 'country_code']);
        $journalTypes = ['general', 'sales', 'purchases', 'bank', 'payroll', 'opening', 'closing'];

        return view('admin.accounting.journal-entries.index', compact('entries', 'companies', 'journalTypes'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        $companyId = $request->get('company');
        $company = $companyId ? Company::find($companyId) : null;

        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);
        $journalTypes = ['general', 'sales', 'purchases', 'bank', 'payroll', 'opening', 'closing'];

        // Récupérer le plan comptable selon le pays
        $accounts = [];
        if ($company) {
            if ($company->country_code === 'TN') {
                $accounts = TunisiaChartOfAccount::active()->postable()->orderBy('account_number')->get();
            } elseif ($company->country_code === 'BE') {
                $accounts = BelgiumChartOfAccount::active()->postable()->orderBy('account_number')->get();
            }
        }

        return view('admin.accounting.journal-entries.create', compact('companies', 'journalTypes', 'accounts', 'company'));
    }

    /**
     * Enregistrer une nouvelle écriture
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:50',
            'journal_type' => 'required|in:general,sales,purchases,bank,payroll,opening,closing',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_number' => 'required|string',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        // Vérifier que débit = crédit
        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()
                ->with('error', "L'écriture n'est pas équilibrée (Débit: {$totalDebit}, Crédit: {$totalCredit})");
        }

        // Vérifier que chaque ligne a soit débit soit crédit (pas les deux)
        foreach ($validated['lines'] as $line) {
            if ($line['debit'] > 0 && $line['credit'] > 0) {
                return back()->withInput()
                    ->with('error', 'Une ligne ne peut pas avoir à la fois un débit et un crédit');
            }
            if ($line['debit'] == 0 && $line['credit'] == 0) {
                return back()->withInput()
                    ->with('error', 'Une ligne doit avoir soit un débit soit un crédit');
            }
        }

        DB::beginTransaction();

        try {
            // Générer une référence si non fournie
            if (empty($validated['reference'])) {
                $company = Company::find($validated['company_id']);
                $date = Carbon::parse($validated['date']);
                $count = JournalEntry::where('company_id', $company->id)
                    ->whereYear('date', $date->year)
                    ->count() + 1;

                $validated['reference'] = sprintf(
                    '%s-%s-%04d',
                    strtoupper(substr($validated['journal_type'], 0, 3)),
                    $date->format('Y'),
                    $count
                );
            }

            // Créer l'écriture
            $entry = JournalEntry::create([
                'company_id' => $validated['company_id'],
                'date' => $validated['date'],
                'reference' => $validated['reference'],
                'journal_type' => $validated['journal_type'],
                'description' => $validated['description'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Créer les lignes
            foreach ($validated['lines'] as $line) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_number' => $line['account_number'],
                    'description' => $line['description'] ?? $validated['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.accounting.journal-entries.show', $entry)
                ->with('success', 'Écriture comptable créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une écriture
     */
    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['company', 'lines']);

        // Vérifier l'équilibre
        $balance = [
            'debit' => $journalEntry->lines->sum('debit'),
            'credit' => $journalEntry->lines->sum('credit'),
            'balanced' => abs($journalEntry->lines->sum('debit') - $journalEntry->lines->sum('credit')) < 0.01,
        ];

        return view('admin.accounting.journal-entries.show', compact('journalEntry', 'balance'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(JournalEntry $journalEntry)
    {
        $journalEntry->load(['company', 'lines']);

        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);
        $journalTypes = ['general', 'sales', 'purchases', 'bank', 'payroll', 'opening', 'closing'];

        // Récupérer le plan comptable selon le pays
        if ($journalEntry->company->country_code === 'TN') {
            $accounts = TunisiaChartOfAccount::active()->postable()->orderBy('account_number')->get();
        } elseif ($journalEntry->company->country_code === 'BE') {
            $accounts = BelgiumChartOfAccount::active()->postable()->orderBy('account_number')->get();
        } else {
            $accounts = [];
        }

        return view('admin.accounting.journal-entries.edit', compact('journalEntry', 'companies', 'journalTypes', 'accounts'));
    }

    /**
     * Mettre à jour une écriture
     */
    public function update(Request $request, JournalEntry $journalEntry)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'reference' => 'required|string|max:50',
            'journal_type' => 'required|in:general,sales,purchases,bank,payroll,opening,closing',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_number' => 'required|string',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        // Vérifier que débit = crédit
        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()
                ->with('error', "L'écriture n'est pas équilibrée (Débit: {$totalDebit}, Crédit: {$totalCredit})");
        }

        DB::beginTransaction();

        try {
            // Mettre à jour l'écriture
            $journalEntry->update([
                'date' => $validated['date'],
                'reference' => $validated['reference'],
                'journal_type' => $validated['journal_type'],
                'description' => $validated['description'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Supprimer les anciennes lignes
            $journalEntry->lines()->delete();

            // Créer les nouvelles lignes
            foreach ($validated['lines'] as $line) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_number' => $line['account_number'],
                    'description' => $line['description'] ?? $validated['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.accounting.journal-entries.show', $journalEntry)
                ->with('success', 'Écriture comptable mise à jour avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une écriture
     */
    public function destroy(JournalEntry $journalEntry)
    {
        DB::beginTransaction();

        try {
            // Supprimer les lignes
            $journalEntry->lines()->delete();

            // Supprimer l'écriture
            $journalEntry->delete();

            DB::commit();

            return redirect()->route('admin.accounting.journal-entries.index')
                ->with('success', 'Écriture comptable supprimée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer les comptes par entreprise (API)
     */
    public function getAccountsByCompany(Company $company)
    {
        if ($company->country_code === 'TN') {
            $accounts = TunisiaChartOfAccount::active()->postable()->orderBy('account_number')->get();
        } elseif ($company->country_code === 'BE') {
            $accounts = BelgiumChartOfAccount::active()->postable()->orderBy('account_number')->get();
        } else {
            $accounts = [];
        }

        return response()->json($accounts);
    }
}
