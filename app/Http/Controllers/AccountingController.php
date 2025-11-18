<?php

namespace App\Http\Controllers;

use App\Models\Modules\Accounting\Models\{Account, Journal, JournalEntry, JournalEntryLine};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AccountingController extends Controller
{
    // ========== ACCOUNTS ==========

    /**
     * Display chart of accounts
     */
    public function accounts(Request $request): Response
    {
        $companyId = session('current_company_id');

        if (!$companyId) {
            return Inertia::render('Accounting/Accounts', [
                'accounts' => []
            ]);
        }

        $query = Account::where('company_id', $companyId);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $accounts = $query->orderBy('code')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Accounting/Accounts', [
            'accounts' => $accounts,
            'filters' => $request->only(['search', 'type'])
        ]);
    }

    /**
     * Store a new account
     */
    public function storeAccount(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code,NULL,id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_account_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['balance'] = 0;

        Account::create($validated);

        return back()->with('success', 'Compte créé avec succès.');
    }

    /**
     * Update an account
     */
    public function updateAccount(Request $request, Account $account)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code,' . $account->id . ',id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_account_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', $account->is_active);

        $account->update($validated);

        return back()->with('success', 'Compte mis à jour avec succès.');
    }

    /**
     * Delete an account
     */
    public function destroyAccount(Account $account)
    {
        // Check if account has entries
        if ($account->debitLines()->exists() || $account->creditLines()->exists()) {
            return back()->withErrors(['error' => 'Ce compte ne peut pas être supprimé car il a des écritures associées.']);
        }

        $account->delete();

        return back()->with('success', 'Compte supprimé avec succès.');
    }

    // ========== JOURNALS ==========

    /**
     * Display journals
     */
    public function journals(): Response
    {
        $companyId = session('current_company_id');

        $journals = Journal::where('company_id', $companyId)
            ->withCount('entries')
            ->orderBy('code')
            ->get();

        return Inertia::render('Accounting/Journals', [
            'journals' => $journals
        ]);
    }

    /**
     * Store a new journal
     */
    public function storeJournal(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:journals,code,NULL,id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'type' => 'required|in:sales,purchases,bank,cash,general',
            'description' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;

        Journal::create($validated);

        return back()->with('success', 'Journal créé avec succès.');
    }

    /**
     * Update a journal
     */
    public function updateJournal(Request $request, Journal $journal)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:journals,code,' . $journal->id . ',id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'type' => 'required|in:sales,purchases,bank,cash,general',
            'description' => 'nullable|string',
        ]);

        $journal->update($validated);

        return back()->with('success', 'Journal mis à jour avec succès.');
    }

    // ========== JOURNAL ENTRIES ==========

    /**
     * Display journal entries
     */
    public function entries(Request $request): Response
    {
        $companyId = session('current_company_id');

        if (!$companyId) {
            return Inertia::render('Accounting/Entries', [
                'entries' => [],
                'journals' => []
            ]);
        }

        $query = JournalEntry::where('company_id', $companyId)
            ->with(['journal', 'lines.debitAccount', 'lines.creditAccount']);

        // Journal filter
        if ($request->filled('journal_id')) {
            $query->where('journal_id', $request->journal_id);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->where('entry_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('entry_date', '<=', $request->to_date);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $entries = $query->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $journals = Journal::where('company_id', $companyId)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Accounting/Entries', [
            'entries' => $entries,
            'journals' => $journals,
            'filters' => $request->only(['journal_id', 'from_date', 'to_date', 'search'])
        ]);
    }

    /**
     * Show form to create journal entry
     */
    public function createEntry(): Response
    {
        $companyId = session('current_company_id');

        $journals = Journal::where('company_id', $companyId)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return Inertia::render('Accounting/CreateEntry', [
            'journals' => $journals,
            'accounts' => $accounts
        ]);
    }

    /**
     * Store a new journal entry
     */
    public function storeEntry(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.debit_account_id' => 'required_without:lines.*.credit_account_id|exists:accounts,id',
            'lines.*.credit_account_id' => 'required_without:lines.*.debit_account_id|exists:accounts,id',
            'lines.*.amount' => 'required|numeric|min:0.01',
            'lines.*.description' => 'nullable|string',
        ]);

        // Validate balanced entry
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($validated['lines'] as $line) {
            if (!empty($line['debit_account_id'])) {
                $totalDebit += $line['amount'];
            }
            if (!empty($line['credit_account_id'])) {
                $totalCredit += $line['amount'];
            }
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['error' => 'L\'écriture n\'est pas équilibrée. Débit: ' . $totalDebit . ', Crédit: ' . $totalCredit]);
        }

        DB::beginTransaction();
        try {
            // Create journal entry
            $entry = JournalEntry::create([
                'company_id' => $companyId,
                'journal_id' => $validated['journal_id'],
                'entry_date' => $validated['entry_date'],
                'reference' => $validated['reference'],
                'description' => $validated['description'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Create lines
            foreach ($validated['lines'] as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'debit_account_id' => $line['debit_account_id'] ?? null,
                    'credit_account_id' => $line['credit_account_id'] ?? null,
                    'amount' => $line['amount'],
                    'description' => $line['description'] ?? null,
                ]);

                // Update account balances
                if (!empty($line['debit_account_id'])) {
                    $account = Account::find($line['debit_account_id']);
                    $account->increment('balance', $line['amount']);
                }

                if (!empty($line['credit_account_id'])) {
                    $account = Account::find($line['credit_account_id']);
                    $account->decrement('balance', $line['amount']);
                }
            }

            DB::commit();

            return redirect()->route('accounting.entries')
                ->with('success', 'Écriture créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Show a journal entry
     */
    public function showEntry(JournalEntry $entry): Response
    {
        $entry->load(['journal', 'lines.debitAccount', 'lines.creditAccount']);

        return Inertia::render('Accounting/ShowEntry', [
            'entry' => $entry
        ]);
    }

    /**
     * Generate general ledger report
     */
    public function generalLedger(Request $request): Response
    {
        $companyId = session('current_company_id');

        $query = Account::where('company_id', $companyId)
            ->with(['debitLines.entry', 'creditLines.entry'])
            ->where('is_active', true);

        // Date range filter
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('debitLines.entry', function($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->where('entry_date', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $q->where('entry_date', '<=', $request->to_date);
                }
            })->orWhereHas('creditLines.entry', function($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->where('entry_date', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $q->where('entry_date', '<=', $request->to_date);
                }
            });
        }

        $accounts = $query->orderBy('code')->get();

        return Inertia::render('Accounting/GeneralLedger', [
            'accounts' => $accounts,
            'filters' => $request->only(['from_date', 'to_date'])
        ]);
    }

    /**
     * Generate trial balance
     */
    public function trialBalance(Request $request): Response
    {
        $companyId = session('current_company_id');

        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return Inertia::render('Accounting/TrialBalance', [
            'accounts' => $accounts,
            'filters' => $request->only(['from_date', 'to_date'])
        ]);
    }
}
