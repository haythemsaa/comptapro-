<?php

namespace App\Services\Belgium;

use App\Models\Company;
use App\Models\Belgium\BelgiumChartOfAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service de génération des rapports financiers Belgique
 * - Bilan (Balance Sheet)
 * - Compte de résultat (Income Statement)
 * - Balance générale (Trial Balance)
 * - Grand livre (General Ledger)
 */
class FinancialReportsBE
{
    /**
     * Générer le bilan (Balance Sheet)
     */
    public function generateBalanceSheet(Company $company, Carbon $date): array
    {
        // ACTIF
        $assets = $this->getAccountBalances($company, $date, ['asset']);
        $currentAssets = $this->getAccountBalances($company, $date, ['asset'], ['4', '5']);
        $fixedAssets = $this->getAccountBalances($company, $date, ['asset'], ['2']);
        $inventory = $this->getAccountBalances($company, $date, ['asset'], ['3']);

        $totalAssets = $assets->sum('balance');

        // PASSIF
        $liabilities = $this->getAccountBalances($company, $date, ['liability']);
        $currentLiabilities = $this->getAccountBalances($company, $date, ['liability'], ['4']);
        $longTermLiabilities = $this->getAccountBalances($company, $date, ['liability'], ['1', '17']);

        $equity = $this->getAccountBalances($company, $date, ['equity']);

        $totalLiabilitiesAndEquity = $liabilities->sum('balance') + $equity->sum('balance');

        return [
            'date' => $date->format('d/m/Y'),
            'actif' => [
                'actif_immobilise' => [
                    'label' => 'Actif immobilisé',
                    'accounts' => $fixedAssets,
                    'total' => $fixedAssets->sum('balance'),
                ],
                'stocks' => [
                    'label' => 'Stocks',
                    'accounts' => $inventory,
                    'total' => $inventory->sum('balance'),
                ],
                'actif_circulant' => [
                    'label' => 'Actif circulant',
                    'accounts' => $currentAssets,
                    'total' => $currentAssets->sum('balance'),
                ],
                'total' => $totalAssets,
            ],
            'passif' => [
                'capitaux_propres' => [
                    'label' => 'Capitaux propres',
                    'accounts' => $equity,
                    'total' => $equity->sum('balance'),
                ],
                'dettes_long_terme' => [
                    'label' => 'Dettes à long terme',
                    'accounts' => $longTermLiabilities,
                    'total' => $longTermLiabilities->sum('balance'),
                ],
                'dettes_court_terme' => [
                    'label' => 'Dettes à court terme',
                    'accounts' => $currentLiabilities,
                    'total' => $currentLiabilities->sum('balance'),
                ],
                'total' => $totalLiabilitiesAndEquity,
            ],
            'equilibre' => abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01,
        ];
    }

    /**
     * Générer le compte de résultat (Income Statement)
     */
    public function generateIncomeStatement(Company $company, Carbon $startDate, Carbon $endDate): array
    {
        // PRODUITS (Classe 7)
        $revenues = $this->getAccountBalances($company, $endDate, ['revenue'], ['7'], $startDate);
        $turnover = $this->getAccountBalances($company, $endDate, ['revenue'], ['70'], $startDate);
        $otherRevenues = $this->getAccountBalances($company, $endDate, ['revenue'], ['74', '75', '76'], $startDate);

        $totalRevenues = $revenues->sum('balance');

        // CHARGES (Classe 6)
        $expenses = $this->getAccountBalances($company, $endDate, ['expense'], ['6'], $startDate);
        $operatingExpenses = $this->getAccountBalances($company, $endDate, ['expense'], ['60', '61', '62'], $startDate);
        $depreciation = $this->getAccountBalances($company, $endDate, ['expense'], ['63'], $startDate);
        $financialExpenses = $this->getAccountBalances($company, $endDate, ['expense'], ['65'], $startDate);
        $taxes = $this->getAccountBalances($company, $endDate, ['expense'], ['67'], $startDate);

        $totalExpenses = $expenses->sum('balance');

        // Résultats
        $operatingResult = $turnover->sum('balance') - $operatingExpenses->sum('balance') - $depreciation->sum('balance');
        $financialResult = $otherRevenues->sum('balance') - $financialExpenses->sum('balance');
        $resultBeforeTax = $operatingResult + $financialResult;
        $netResult = $resultBeforeTax - $taxes->sum('balance');

        return [
            'period' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y'),
            ],
            'produits' => [
                'chiffre_affaires' => [
                    'label' => 'Chiffre d\'affaires',
                    'accounts' => $turnover,
                    'total' => $turnover->sum('balance'),
                ],
                'autres_produits' => [
                    'label' => 'Autres produits',
                    'accounts' => $otherRevenues,
                    'total' => $otherRevenues->sum('balance'),
                ],
                'total' => $totalRevenues,
            ],
            'charges' => [
                'charges_exploitation' => [
                    'label' => 'Charges d\'exploitation',
                    'accounts' => $operatingExpenses,
                    'total' => $operatingExpenses->sum('balance'),
                ],
                'amortissements' => [
                    'label' => 'Amortissements',
                    'accounts' => $depreciation,
                    'total' => $depreciation->sum('balance'),
                ],
                'charges_financieres' => [
                    'label' => 'Charges financières',
                    'accounts' => $financialExpenses,
                    'total' => $financialExpenses->sum('balance'),
                ],
                'impots' => [
                    'label' => 'Impôts',
                    'accounts' => $taxes,
                    'total' => $taxes->sum('balance'),
                ],
                'total' => $totalExpenses,
            ],
            'resultats' => [
                'resultat_exploitation' => $operatingResult,
                'resultat_financier' => $financialResult,
                'resultat_avant_impots' => $resultBeforeTax,
                'resultat_net' => $netResult,
            ],
            'ratios' => [
                'marge_brute' => $turnover->sum('balance') > 0 ? ($operatingResult / $turnover->sum('balance')) * 100 : 0,
                'marge_nette' => $totalRevenues > 0 ? ($netResult / $totalRevenues) * 100 : 0,
            ],
        ];
    }

    /**
     * Générer la balance générale
     */
    public function generateTrialBalance(Company $company, Carbon $date): array
    {
        $accounts = BelgiumChartOfAccount::active()
            ->postable()
            ->orderBy('account_number')
            ->get();

        $balances = [];
        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($company, $account->account_number, $date);

            if (abs($balance['debit']) > 0.01 || abs($balance['credit']) > 0.01 || abs($balance['balance']) > 0.01) {
                $balances[] = [
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'debit' => $balance['debit'],
                    'credit' => $balance['credit'],
                    'balance' => $balance['balance'],
                ];
            }
        }

        $totalDebit = collect($balances)->sum('debit');
        $totalCredit = collect($balances)->sum('credit');

        return [
            'date' => $date->format('d/m/Y'),
            'accounts' => $balances,
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'difference' => $totalDebit - $totalCredit,
                'balanced' => abs($totalDebit - $totalCredit) < 0.01,
            ],
        ];
    }

    /**
     * Récupérer les soldes des comptes
     */
    protected function getAccountBalances(
        Company $company,
        Carbon $date,
        array $types,
        array $classes = null,
        Carbon $startDate = null
    ) {
        $query = DB::table('journal_entries as je')
            ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
            ->join('belgium_chart_of_accounts as acc', 'jl.account_number', '=', 'acc.account_number')
            ->where('je.company_id', $company->id)
            ->whereDate('je.date', '<=', $date)
            ->whereIn('acc.type', $types);

        if ($startDate) {
            $query->whereDate('je.date', '>=', $startDate);
        }

        if ($classes) {
            $query->where(function ($q) use ($classes) {
                foreach ($classes as $class) {
                    $q->orWhere('acc.account_number', 'like', $class . '%');
                }
            });
        }

        return $query->select(
            'acc.account_number',
            'acc.account_name',
            DB::raw('SUM(jl.debit) as total_debit'),
            DB::raw('SUM(jl.credit) as total_credit'),
            DB::raw('SUM(jl.debit - jl.credit) as balance')
        )
            ->groupBy('acc.account_number', 'acc.account_name')
            ->having(DB::raw('ABS(SUM(jl.debit - jl.credit))'), '>', 0.01)
            ->get();
    }

    /**
     * Récupérer le solde d'un compte spécifique
     */
    protected function getAccountBalance(Company $company, string $accountNumber, Carbon $date): array
    {
        $result = DB::table('journal_entries as je')
            ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
            ->where('je.company_id', $company->id)
            ->where('jl.account_number', $accountNumber)
            ->whereDate('je.date', '<=', $date)
            ->select(
                DB::raw('SUM(jl.debit) as total_debit'),
                DB::raw('SUM(jl.credit) as total_credit')
            )
            ->first();

        $debit = $result->total_debit ?? 0;
        $credit = $result->total_credit ?? 0;
        $balance = $debit - $credit;

        return [
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $balance,
        ];
    }
}
