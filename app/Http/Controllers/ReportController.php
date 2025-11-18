<?php

namespace App\Http\Controllers;

use App\Models\Modules\Accounting\Models\{Account, JournalEntry};
use App\Models\Modules\Invoicing\Models\{Customer, Invoice};
use App\Models\Modules\Core\Models\Company;
use App\Services\PdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index(): Response
    {
        return Inertia::render('Reports/Index');
    }

    /**
     * Profit & Loss Statement (Income Statement)
     */
    public function profitLoss(Request $request): Response
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        // Default to current fiscal year
        $fromDate = $request->input('from_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        // Get revenue accounts
        $revenueAccounts = Account::where('company_id', $companyId)
            ->where('type', 'revenue')
            ->with(['creditLines' => function($q) use ($fromDate, $toDate) {
                $q->whereHas('entry', function($q) use ($fromDate, $toDate) {
                    $q->whereBetween('entry_date', [$fromDate, $toDate]);
                });
            }, 'debitLines' => function($q) use ($fromDate, $toDate) {
                $q->whereHas('entry', function($q) use ($fromDate, $toDate) {
                    $q->whereBetween('entry_date', [$fromDate, $toDate]);
                });
            }])
            ->get();

        // Get expense accounts
        $expenseAccounts = Account::where('company_id', $companyId)
            ->where('type', 'expense')
            ->with(['debitLines' => function($q) use ($fromDate, $toDate) {
                $q->whereHas('entry', function($q) use ($fromDate, $toDate) {
                    $q->whereBetween('entry_date', [$fromDate, $toDate]);
                });
            }, 'creditLines' => function($q) use ($fromDate, $toDate) {
                $q->whereHas('entry', function($q) use ($fromDate, $toDate) {
                    $q->whereBetween('entry_date', [$fromDate, $toDate]);
                });
            }])
            ->get();

        // Calculate totals
        $totalRevenue = 0;
        foreach ($revenueAccounts as $account) {
            $credits = $account->creditLines->sum('amount');
            $debits = $account->debitLines->sum('amount');
            $totalRevenue += ($credits - $debits);
        }

        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            $debits = $account->debitLines->sum('amount');
            $credits = $account->creditLines->sum('amount');
            $totalExpenses += ($debits - $credits);
        }

        $netIncome = $totalRevenue - $totalExpenses;

        return Inertia::render('Reports/ProfitLoss', [
            'company' => $company,
            'revenueAccounts' => $revenueAccounts,
            'expenseAccounts' => $expenseAccounts,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $netIncome,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    /**
     * Balance Sheet
     */
    public function balanceSheet(Request $request): Response
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        $asOfDate = $request->input('as_of_date', Carbon::now()->format('Y-m-d'));

        // Get asset accounts
        $assetAccounts = Account::where('company_id', $companyId)
            ->where('type', 'asset')
            ->where('is_active', true)
            ->get();

        // Get liability accounts
        $liabilityAccounts = Account::where('company_id', $companyId)
            ->where('type', 'liability')
            ->where('is_active', true)
            ->get();

        // Get equity accounts
        $equityAccounts = Account::where('company_id', $companyId)
            ->where('type', 'equity')
            ->where('is_active', true)
            ->get();

        $totalAssets = $assetAccounts->sum('balance');
        $totalLiabilities = $liabilityAccounts->sum('balance');
        $totalEquity = $equityAccounts->sum('balance');

        return Inertia::render('Reports/BalanceSheet', [
            'company' => $company,
            'assetAccounts' => $assetAccounts,
            'liabilityAccounts' => $liabilityAccounts,
            'equityAccounts' => $equityAccounts,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'asOfDate' => $asOfDate,
        ]);
    }

    /**
     * VAT Report
     */
    public function vatReport(Request $request): Response
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        // Default to current quarter
        $fromDate = $request->input('from_date', Carbon::now()->startOfQuarter()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->endOfQuarter()->format('Y-m-d'));

        // Get sales invoices (VAT collected)
        $salesInvoices = Invoice::where('company_id', $companyId)
            ->whereIn('type', ['invoice'])
            ->whereIn('status', ['sent', 'paid'])
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->with('customer')
            ->get();

        $vatCollected = $salesInvoices->sum('tax_amount');
        $salesTotal = $salesInvoices->sum('subtotal');

        // Group by VAT rate
        $vatByRate = [];
        foreach ($salesInvoices as $invoice) {
            foreach ($invoice->lines as $line) {
                $rate = $line->vat_rate;
                if (!isset($vatByRate[$rate])) {
                    $vatByRate[$rate] = [
                        'rate' => $rate,
                        'base' => 0,
                        'vat' => 0,
                    ];
                }
                $vatByRate[$rate]['base'] += $line->subtotal;
                $vatByRate[$rate]['vat'] += $line->tax_amount;
            }
        }

        // TODO: Add purchases VAT (VAT paid) when purchase module is implemented
        $vatPaid = 0;
        $purchasesTotal = 0;

        $vatDue = $vatCollected - $vatPaid;

        return Inertia::render('Reports/VatReport', [
            'company' => $company,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'vatCollected' => $vatCollected,
            'vatPaid' => $vatPaid,
            'vatDue' => $vatDue,
            'salesTotal' => $salesTotal,
            'purchasesTotal' => $purchasesTotal,
            'vatByRate' => array_values($vatByRate),
            'salesInvoices' => $salesInvoices,
        ]);
    }

    /**
     * Customer Statement
     */
    public function customerStatement(Request $request, Customer $customer): Response
    {
        $companyId = session('current_company_id');

        $fromDate = $request->input('from_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->orderBy('issue_date')
            ->get();

        $totalInvoiced = $invoices->sum('total');
        $totalPaid = $invoices->sum('paid_amount');
        $balance = $totalInvoiced - $totalPaid;

        return Inertia::render('Reports/CustomerStatement', [
            'customer' => $customer,
            'invoices' => $invoices,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'balance' => $balance,
        ]);
    }

    /**
     * Aged Receivables Report
     */
    public function agedReceivables(Request $request): Response
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        $invoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->with('customer')
            ->get();

        $today = Carbon::now();

        // Categorize by age
        $aged = [
            'current' => [],
            '1-30' => [],
            '31-60' => [],
            '61-90' => [],
            '90+' => [],
        ];

        $totals = [
            'current' => 0,
            '1-30' => 0,
            '31-60' => 0,
            '61-90' => 0,
            '90+' => 0,
        ];

        foreach ($invoices as $invoice) {
            $dueDate = Carbon::parse($invoice->due_date);
            $daysOverdue = $today->diffInDays($dueDate, false);
            $balance = $invoice->total - $invoice->paid_amount;

            if ($daysOverdue >= 0) {
                $aged['current'][] = $invoice;
                $totals['current'] += $balance;
            } elseif ($daysOverdue >= -30) {
                $aged['1-30'][] = $invoice;
                $totals['1-30'] += $balance;
            } elseif ($daysOverdue >= -60) {
                $aged['31-60'][] = $invoice;
                $totals['31-60'] += $balance;
            } elseif ($daysOverdue >= -90) {
                $aged['61-90'][] = $invoice;
                $totals['61-90'] += $balance;
            } else {
                $aged['90+'][] = $invoice;
                $totals['90+'] += $balance;
            }
        }

        $grandTotal = array_sum($totals);

        return Inertia::render('Reports/AgedReceivables', [
            'company' => $company,
            'aged' => $aged,
            'totals' => $totals,
            'grandTotal' => $grandTotal,
        ]);
    }

    /**
     * Sales by Customer Report
     */
    public function salesByCustomer(Request $request): Response
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        $fromDate = $request->input('from_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        $customerSales = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.company_id', $companyId)
            ->whereIn('invoices.status', ['sent', 'paid'])
            ->whereBetween('invoices.issue_date', [$fromDate, $toDate])
            ->select(
                'customers.id',
                'customers.name',
                'customers.customer_number',
                DB::raw('COUNT(invoices.id) as invoice_count'),
                DB::raw('SUM(invoices.subtotal) as total_sales'),
                DB::raw('SUM(invoices.tax_amount) as total_vat'),
                DB::raw('SUM(invoices.total) as total_amount')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.customer_number')
            ->orderBy('total_amount', 'desc')
            ->get();

        $grandTotal = $customerSales->sum('total_amount');

        return Inertia::render('Reports/SalesByCustomer', [
            'company' => $company,
            'customerSales' => $customerSales,
            'grandTotal' => $grandTotal,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    /**
     * Cash Flow Statement (Basic)
     */
    public function cashFlow(Request $request): Response
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        $fromDate = $request->input('from_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        // Operating activities (simplified)
        $cashFromSales = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->sum('paid_amount');

        // TODO: Add cash paid for expenses when expense module is implemented
        $cashPaidForExpenses = 0;

        $netCashFromOperating = $cashFromSales - $cashPaidForExpenses;

        // Investing activities
        // TODO: Add investing activities when asset module is implemented
        $netCashFromInvesting = 0;

        // Financing activities
        // TODO: Add financing activities when banking module is implemented
        $netCashFromFinancing = 0;

        $netCashChange = $netCashFromOperating + $netCashFromInvesting + $netCashFromFinancing;

        return Inertia::render('Reports/CashFlow', [
            'company' => $company,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'cashFromSales' => $cashFromSales,
            'cashPaidForExpenses' => $cashPaidForExpenses,
            'netCashFromOperating' => $netCashFromOperating,
            'netCashFromInvesting' => $netCashFromInvesting,
            'netCashFromFinancing' => $netCashFromFinancing,
            'netCashChange' => $netCashChange,
        ]);
    }

    /**
     * Detailed Balance with Year-over-Year Comparison
     */
    public function detailedBalance(Request $request): Response
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        $currentYear = $request->input('year', Carbon::now()->year);
        $previousYear = $currentYear - 1;

        // Get all accounts with their balances
        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $balanceData = [];

        foreach ($accounts as $account) {
            // Calculate current year balance
            $currentYearBalance = $this->calculateAccountBalanceForYear($account, $currentYear);

            // Calculate previous year balance
            $previousYearBalance = $this->calculateAccountBalanceForYear($account, $previousYear);

            // Calculate variance
            $variance = $currentYearBalance - $previousYearBalance;
            $variancePercent = $previousYearBalance != 0
                ? (($variance / abs($previousYearBalance)) * 100)
                : 0;

            $balanceData[] = [
                'account' => $account,
                'current_year' => $currentYearBalance,
                'previous_year' => $previousYearBalance,
                'variance' => $variance,
                'variance_percent' => $variancePercent,
            ];
        }

        // Group by account type
        $groupedData = collect($balanceData)->groupBy('account.type');

        return Inertia::render('Reports/DetailedBalance', [
            'company' => $company,
            'currentYear' => $currentYear,
            'previousYear' => $previousYear,
            'balanceData' => $groupedData,
        ]);
    }

    /**
     * Helper: Calculate account balance for a specific year
     */
    private function calculateAccountBalanceForYear(Account $account, int $year): float
    {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        $debits = JournalEntryLine::where('account_id', $account->id)
            ->where('type', 'debit')
            ->whereHas('entry', function($q) use ($startDate, $endDate) {
                $q->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->sum('amount');

        $credits = JournalEntryLine::where('account_id', $account->id)
            ->where('type', 'credit')
            ->whereHas('entry', function($q) use ($startDate, $endDate) {
                $q->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->sum('amount');

        // Calculate balance based on account type
        if (in_array($account->type, ['asset', 'expense'])) {
            return $debits - $credits;
        } else {
            return $credits - $debits;
        }
    }

    /**
     * Download Profit & Loss as PDF
     */
    public function profitLossPdf(Request $request, PdfGenerator $pdfGenerator)
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        $fromDate = $request->input('from_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        $revenueAccounts = Account::where('company_id', $companyId)
            ->where('type', 'revenue')
            ->orderBy('code')
            ->get();

        $expenseAccounts = Account::where('company_id', $companyId)
            ->where('type', 'expense')
            ->orderBy('code')
            ->get();

        // Calculate balances
        foreach ($revenueAccounts as $account) {
            $account->balance = $this->calculateAccountBalance($account, $fromDate, $toDate);
        }

        foreach ($expenseAccounts as $account) {
            $account->balance = $this->calculateAccountBalance($account, $fromDate, $toDate);
        }

        $data = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'revenueAccounts' => $revenueAccounts,
            'expenseAccounts' => $expenseAccounts,
        ];

        $pdf = $pdfGenerator->generateProfitLossPdf($company, $data);
        return $pdf->download('profit-loss_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Download VAT Report as PDF
     */
    public function vatReportPdf(Request $request, PdfGenerator $pdfGenerator)
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        // Calculate VAT by rate
        $invoiceLines = DB::table('invoice_lines')
            ->join('invoices', 'invoice_lines.invoice_id', '=', 'invoices.id')
            ->where('invoices.company_id', $companyId)
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->whereIn('invoices.status', ['sent', 'paid'])
            ->select('invoice_lines.vat_rate',
                     DB::raw('SUM(invoice_lines.subtotal) as base_ht'),
                     DB::raw('SUM(invoice_lines.tax_amount) as vat'),
                     DB::raw('SUM(invoice_lines.total) as total_ttc'))
            ->groupBy('invoice_lines.vat_rate')
            ->get();

        $vatByRate = [];
        foreach ($invoiceLines as $line) {
            $vatByRate[$line->vat_rate] = [
                'base_ht' => $line->base_ht,
                'vat' => $line->vat,
                'total_ttc' => $line->total_ttc,
            ];
        }

        $data = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'vatByRate' => $vatByRate,
            'vatDeductible' => 0, // TODO: Calculate from purchases
        ];

        $pdf = $pdfGenerator->generateVatReportPdf($company, $data);
        return $pdf->download('vat-report_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Download Balance Sheet as PDF
     */
    public function balanceSheetPdf(Request $request, PdfGenerator $pdfGenerator)
    {
        $companyId = session('current_company_id');
        $company = Company::with('country')->find($companyId);

        $asOfDate = $request->input('as_of_date', Carbon::now()->format('Y-m-d'));

        // Get asset accounts
        $assetAccounts = Account::where('company_id', $companyId)
            ->where('type', 'asset')
            ->where('is_active', true)
            ->get();

        // Get liability accounts
        $liabilityAccounts = Account::where('company_id', $companyId)
            ->where('type', 'liability')
            ->where('is_active', true)
            ->get();

        // Get equity accounts
        $equityAccounts = Account::where('company_id', $companyId)
            ->where('type', 'equity')
            ->where('is_active', true)
            ->get();

        $totalAssets = $assetAccounts->sum('balance');
        $totalLiabilities = $liabilityAccounts->sum('balance');
        $totalEquity = $equityAccounts->sum('balance');

        $data = [
            'asOfDate' => $asOfDate,
            'assetAccounts' => $assetAccounts,
            'liabilityAccounts' => $liabilityAccounts,
            'equityAccounts' => $equityAccounts,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
        ];

        $pdf = $pdfGenerator->generateBalanceSheetPdf($company, $data);
        return $pdf->download('balance-sheet_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Helper: Calculate account balance for a period
     */
    private function calculateAccountBalance(Account $account, $fromDate, $toDate): float
    {
        $debits = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $account->id)
            ->where('journal_entry_lines.type', 'debit')
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->sum('journal_entry_lines.amount');

        $credits = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $account->id')
            ->where('journal_entry_lines.type', 'credit')
            ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
            ->sum('journal_entry_lines.amount');

        if ($account->type === 'revenue') {
            return $credits - $debits;
        } else {
            return $debits - $credits;
        }
    }
}
