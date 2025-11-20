<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Models\Banking\BankAccount;
use App\Models\Banking\BankReconciliation;
use App\Models\Banking\BankTransaction;
use App\Services\Banking\BankReconciliationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankReconciliationController extends Controller
{
    /**
     * Liste des comptes bancaires
     */
    public function accounts(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        $accounts = BankAccount::where('company_id', $companyId)
            ->with(['transactions' => function ($query) {
                $query->where('is_reconciled', false)->limit(5);
            }])
            ->get();

        return Inertia::render('Banking/Accounts', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Import d'un relevé bancaire
     */
    public function importStatement(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'file' => 'required|file|mimes:csv,ofx,qif|max:5120',
            'format' => 'required|in:csv,ofx,qif',
        ]);

        $companyId = $request->user()->current_company_id;
        $file = $request->file('file');
        $path = $file->store('temp/bank-statements');
        $fullPath = storage_path('app/' . $path);

        $service = new BankReconciliationService($companyId);
        $result = $service->importBankStatement(
            $validated['bank_account_id'],
            $fullPath,
            $validated['format']
        );

        // Nettoyer le fichier temporaire
        unlink($fullPath);

        if (!$result['success']) {
            return back()->withErrors(['message' => $result['error']]);
        }

        return redirect()->route('banking.reconciliation', ['account' => $validated['bank_account_id']])
            ->with('success', "{$result['imported_count']} transactions importées");
    }

    /**
     * Page de rapprochement bancaire
     */
    public function reconciliation(Request $request, int $accountId)
    {
        $companyId = $request->user()->current_company_id;
        $account = BankAccount::where('company_id', $companyId)
            ->findOrFail($accountId);

        // Transactions non rapprochées
        $unmatchedTransactions = BankTransaction::where('company_id', $companyId)
            ->where('bank_account_id', $accountId)
            ->where('is_reconciled', false)
            ->orderBy('transaction_date', 'desc')
            ->limit(50)
            ->get();

        // Derniers rapprochements
        $recentReconciliations = BankReconciliation::where('company_id', $companyId)
            ->where('bank_account_id', $accountId)
            ->orderBy('reconciliation_date', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Banking/Reconciliation', [
            'account' => $account,
            'unmatched_transactions' => $unmatchedTransactions,
            'recent_reconciliations' => $recentReconciliations,
        ]);
    }

    /**
     * Effectue le rapprochement automatique
     */
    public function performReconciliation(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $companyId = $request->user()->current_company_id;

        $service = new BankReconciliationService($companyId);
        $result = $service->reconcile(
            $validated['bank_account_id'],
            $validated['start_date'],
            $validated['end_date']
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 500);
        }

        // Enregistrer le rapprochement
        $reconciliation = BankReconciliation::create([
            'company_id' => $companyId,
            'bank_account_id' => $validated['bank_account_id'],
            'reconciliation_date' => now(),
            'period_start' => $validated['start_date'],
            'period_end' => $validated['end_date'],
            'opening_balance_accounting' => $result['balances']['accounting_balance'] ?? 0,
            'opening_balance_bank' => $result['balances']['bank_balance'] ?? 0,
            'ending_balance_accounting' => $result['balances']['accounting_balance'] ?? 0,
            'ending_balance_bank' => $result['balances']['bank_balance'] ?? 0,
            'difference' => $result['balances']['difference'] ?? 0,
            'matched_count' => $result['matched_count'],
            'unmatched_bank_count' => $result['unmatched_bank_count'],
            'unmatched_accounting_count' => $result['unmatched_accounting_count'],
            'matched_amount' => collect($result['matched_transactions'])->sum('bank_transaction.amount'),
            'status' => 'completed',
            'reconciliation_data' => $result,
        ]);

        return response()->json([
            'success' => true,
            'reconciliation' => $reconciliation,
            'result' => $result,
        ]);
    }

    /**
     * Rapprochement manuel d'une transaction
     */
    public function manualMatch(Request $request)
    {
        $validated = $request->validate([
            'bank_transaction_id' => 'required|exists:bank_transactions,id',
            'journal_entry_id' => 'required|exists:journal_entries,id',
        ]);

        $companyId = $request->user()->current_company_id;
        $userId = $request->user()->id;

        $transaction = BankTransaction::where('company_id', $companyId)
            ->findOrFail($validated['bank_transaction_id']);

        $transaction->reconcile($validated['journal_entry_id'], $userId, 'manual');

        return response()->json([
            'success' => true,
            'message' => 'Transaction rapprochée manuellement',
        ]);
    }

    /**
     * Annule un rapprochement
     */
    public function unmatch(Request $request)
    {
        $validated = $request->validate([
            'bank_transaction_id' => 'required|exists:bank_transactions,id',
        ]);

        $companyId = $request->user()->current_company_id;

        $transaction = BankTransaction::where('company_id', $companyId)
            ->findOrFail($validated['bank_transaction_id']);

        $transaction->unreconcile();

        return response()->json([
            'success' => true,
            'message' => 'Rapprochement annulé',
        ]);
    }

    /**
     * Approuve un rapprochement
     */
    public function approve(Request $request, int $reconciliationId)
    {
        $companyId = $request->user()->current_company_id;
        $userId = $request->user()->id;

        $reconciliation = BankReconciliation::where('company_id', $companyId)
            ->findOrFail($reconciliationId);

        $reconciliation->approve($userId);

        return redirect()->route('banking.reconciliation', ['account' => $reconciliation->bank_account_id])
            ->with('success', 'Rapprochement approuvé');
    }

    /**
     * Statistiques de rapprochement
     */
    public function stats(Request $request, int $accountId)
    {
        $companyId = $request->user()->current_company_id;

        $stats = [
            'total_transactions' => BankTransaction::where('company_id', $companyId)
                ->where('bank_account_id', $accountId)
                ->count(),
            'reconciled_transactions' => BankTransaction::where('company_id', $companyId)
                ->where('bank_account_id', $accountId)
                ->where('is_reconciled', true)
                ->count(),
            'unreconciled_transactions' => BankTransaction::where('company_id', $companyId)
                ->where('bank_account_id', $accountId)
                ->where('is_reconciled', false)
                ->count(),
            'reconciliations_count' => BankReconciliation::where('company_id', $companyId)
                ->where('bank_account_id', $accountId)
                ->count(),
            'last_reconciliation' => BankReconciliation::where('company_id', $companyId)
                ->where('bank_account_id', $accountId)
                ->orderBy('reconciliation_date', 'desc')
                ->first(),
        ];

        $stats['reconciliation_rate'] = $stats['total_transactions'] > 0
            ? ($stats['reconciled_transactions'] / $stats['total_transactions']) * 100
            : 0;

        return response()->json($stats);
    }
}
