<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Belgium\FinancialReportsBE;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Contrôleur de génération des rapports financiers
 * Compatible Tunisia (PCN) et Belgium (PCMN)
 */
class AccountingReportsController extends Controller
{
    /**
     * Dashboard des rapports
     */
    public function index()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);

        return view('admin.accounting.reports.index', compact('companies'));
    }

    /**
     * Bilan (Balance Sheet)
     */
    public function balanceSheet(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required|date',
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $date = Carbon::parse($validated['date']);

        $report = null;
        $error = null;

        try {
            if ($company->country_code === 'BE') {
                $service = app(FinancialReportsBE::class);
                $report = $service->generateBalanceSheet($company, $date);
            } elseif ($company->country_code === 'TN') {
                // TODO: Implémenter FinancialReportsTN
                $error = 'Rapport non disponible pour la Tunisie (à implémenter)';
            } else {
                $error = 'Pays non supporté';
            }
        } catch (\Exception $e) {
            $error = 'Erreur lors de la génération du rapport: ' . $e->getMessage();
        }

        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);

        return view('admin.accounting.reports.balance-sheet', compact('report', 'company', 'date', 'companies', 'error'));
    }

    /**
     * Compte de résultat (Income Statement)
     */
    public function incomeStatement(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $report = null;
        $error = null;

        try {
            if ($company->country_code === 'BE') {
                $service = app(FinancialReportsBE::class);
                $report = $service->generateIncomeStatement($company, $startDate, $endDate);
            } elseif ($company->country_code === 'TN') {
                // TODO: Implémenter FinancialReportsTN
                $error = 'Rapport non disponible pour la Tunisie (à implémenter)';
            } else {
                $error = 'Pays non supporté';
            }
        } catch (\Exception $e) {
            $error = 'Erreur lors de la génération du rapport: ' . $e->getMessage();
        }

        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);

        return view('admin.accounting.reports.income-statement', compact('report', 'company', 'startDate', 'endDate', 'companies', 'error'));
    }

    /**
     * Balance générale (Trial Balance)
     */
    public function trialBalance(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required|date',
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $date = Carbon::parse($validated['date']);

        $report = null;
        $error = null;

        try {
            if ($company->country_code === 'BE') {
                $service = app(FinancialReportsBE::class);
                $report = $service->generateTrialBalance($company, $date);
            } elseif ($company->country_code === 'TN') {
                // TODO: Implémenter FinancialReportsTN
                $error = 'Rapport non disponible pour la Tunisie (à implémenter)';
            } else {
                $error = 'Pays non supporté';
            }
        } catch (\Exception $e) {
            $error = 'Erreur lors de la génération du rapport: ' . $e->getMessage();
        }

        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);

        return view('admin.accounting.reports.trial-balance', compact('report', 'company', 'date', 'companies', 'error'));
    }

    /**
     * Grand livre (General Ledger)
     */
    public function generalLedger(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'account_number' => 'nullable|string',
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // Récupérer les écritures
        $query = \App\Models\JournalEntry::with('lines')
            ->where('company_id', $company->id)
            ->whereBetween('date', [$startDate, $endDate]);

        if (!empty($validated['account_number'])) {
            $query->whereHas('lines', function ($q) use ($validated) {
                $q->where('account_number', $validated['account_number']);
            });
        }

        $entries = $query->orderBy('date')->get();

        // Organiser par compte
        $ledger = [];
        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                if (!empty($validated['account_number']) && $line->account_number !== $validated['account_number']) {
                    continue;
                }

                if (!isset($ledger[$line->account_number])) {
                    $ledger[$line->account_number] = [
                        'account_number' => $line->account_number,
                        'entries' => [],
                        'total_debit' => 0,
                        'total_credit' => 0,
                    ];
                }

                $ledger[$line->account_number]['entries'][] = [
                    'date' => $entry->date,
                    'reference' => $entry->reference,
                    'description' => $line->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ];

                $ledger[$line->account_number]['total_debit'] += $line->debit;
                $ledger[$line->account_number]['total_credit'] += $line->credit;
            }
        }

        // Calculer les soldes
        foreach ($ledger as &$account) {
            $account['balance'] = $account['total_debit'] - $account['total_credit'];
        }

        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);

        return view('admin.accounting.reports.general-ledger', compact('ledger', 'company', 'startDate', 'endDate', 'companies'));
    }

    /**
     * Export PDF
     */
    public function exportPDF(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:balance_sheet,income_statement,trial_balance,general_ledger',
            'company_id' => 'required|exists:companies,id',
            'date' => 'required_if:report_type,balance_sheet,trial_balance|nullable|date',
            'start_date' => 'required_if:report_type,income_statement,general_ledger|nullable|date',
            'end_date' => 'required_if:report_type,income_statement,general_ledger|nullable|date',
        ]);

        // TODO: Implémenter l'export PDF
        return back()->with('info', 'Export PDF à implémenter');
    }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:balance_sheet,income_statement,trial_balance,general_ledger',
            'company_id' => 'required|exists:companies,id',
            'date' => 'required_if:report_type,balance_sheet,trial_balance|nullable|date',
            'start_date' => 'required_if:report_type,income_statement,general_ledger|nullable|date',
            'end_date' => 'required_if:report_type,income_statement,general_ledger|nullable|date',
        ]);

        // TODO: Implémenter l'export Excel
        return back()->with('info', 'Export Excel à implémenter');
    }

    /**
     * Tableau de bord financier
     */
    public function dashboard(Request $request)
    {
        $companyId = $request->get('company_id');

        if (!$companyId) {
            $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);
            return view('admin.accounting.reports.dashboard', compact('companies'));
        }

        $company = Company::findOrFail($companyId);
        $date = Carbon::now();

        // Stats générales
        $stats = [
            'total_entries' => \App\Models\JournalEntry::where('company_id', $company->id)->count(),
            'entries_this_month' => \App\Models\JournalEntry::where('company_id', $company->id)
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->count(),
            'total_accounts_used' => \App\Models\JournalLine::whereHas('journalEntry', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            })->distinct('account_number')->count(),
        ];

        // Dernières écritures
        $recentEntries = \App\Models\JournalEntry::where('company_id', $company->id)
            ->with('lines')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_code']);

        return view('admin.accounting.reports.dashboard', compact('company', 'stats', 'recentEntries', 'companies'));
    }
}
