<?php

namespace App\Services\AI;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use App\Helpers\TunisiaHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Anthropic\Anthropic;
use Carbon\Carbon;

/**
 * Service d'automatisation des rapports financiers avec IA
 * Génère automatiquement le bilan, compte de résultat et analyses
 */
class AutoFinancialReportsService
{
    private Anthropic $anthropic;
    private int $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
        $this->anthropic = new Anthropic(config('services.anthropic.key'));
    }

    /**
     * Génère automatiquement tous les états financiers
     */
    public function generateAllFinancialReports(int $year, ?int $month = null): array
    {
        try {
            $startDate = $month
                ? Carbon::create($year, $month, 1)->startOfMonth()
                : Carbon::create($year, 1, 1)->startOfYear();

            $endDate = $month
                ? Carbon::create($year, $month, 1)->endOfMonth()
                : Carbon::create($year, 12, 31)->endOfYear();

            return [
                'balance_sheet' => $this->generateBalanceSheet($endDate),
                'income_statement' => $this->generateIncomeStatement($startDate, $endDate),
                'cash_flow_statement' => $this->generateCashFlowStatement($startDate, $endDate),
                'financial_ratios' => $this->calculateFinancialRatios($endDate),
                'ai_analysis' => $this->generateAIAnalysis($endDate),
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Financial Reports Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Génère le bilan comptable automatiquement
     */
    public function generateBalanceSheet(Carbon $asOfDate): array
    {
        // Actif
        $assets = [
            'immobilisations' => $this->getAccountsBalance(['2'], $asOfDate, 'debit'),
            'stocks' => $this->getAccountsBalance(['3'], $asOfDate, 'debit'),
            'creances_clients' => $this->getAccountsBalance(['411'], $asOfDate, 'debit'),
            'autres_creances' => $this->getAccountsBalance(['41', '42', '43'], $asOfDate, 'debit', ['411']),
            'tresorerie_actif' => $this->getAccountsBalance(['5'], $asOfDate, 'debit'),
        ];

        $assets['total_actif_immobilise'] = $assets['immobilisations'];
        $assets['total_actif_circulant'] = $assets['stocks'] + $assets['creances_clients']
            + $assets['autres_creances'] + $assets['tresorerie_actif'];
        $assets['total_actif'] = $assets['total_actif_immobilise'] + $assets['total_actif_circulant'];

        // Passif
        $liabilities = [
            'capital' => $this->getAccountsBalance(['101'], $asOfDate, 'credit'),
            'reserves' => $this->getAccountsBalance(['106', '11'], $asOfDate, 'credit'),
            'resultat' => $this->calculateNetIncome($asOfDate),
            'dettes_fournisseurs' => $this->getAccountsBalance(['401'], $asOfDate, 'credit'),
            'dettes_fiscales' => $this->getAccountsBalance(['44'], $asOfDate, 'credit'),
            'dettes_sociales' => $this->getAccountsBalance(['43'], $asOfDate, 'credit'),
            'emprunts' => $this->getAccountsBalance(['16', '50'], $asOfDate, 'credit'),
            'autres_dettes' => $this->getAccountsBalance(['40', '42', '45', '46', '47'], $asOfDate, 'credit', ['401']),
        ];

        $liabilities['total_capitaux_propres'] = $liabilities['capital'] + $liabilities['reserves'] + $liabilities['resultat'];
        $liabilities['total_dettes'] = $liabilities['dettes_fournisseurs'] + $liabilities['dettes_fiscales']
            + $liabilities['dettes_sociales'] + $liabilities['emprunts'] + $liabilities['autres_dettes'];
        $liabilities['total_passif'] = $liabilities['total_capitaux_propres'] + $liabilities['total_dettes'];

        return [
            'date' => $asOfDate->format('Y-m-d'),
            'actif' => $assets,
            'passif' => $liabilities,
            'equilibre' => abs($assets['total_actif'] - $liabilities['total_passif']) < 0.01
        ];
    }

    /**
     * Génère le compte de résultat automatiquement
     */
    public function generateIncomeStatement(Carbon $startDate, Carbon $endDate): array
    {
        // Produits (Classe 7)
        $revenues = [
            'ventes_marchandises' => $this->getAccountsBalance(['707'], $endDate, 'credit', [], $startDate),
            'ventes_produits_finis' => $this->getAccountsBalance(['701', '702'], $endDate, 'credit', [], $startDate),
            'prestations_services' => $this->getAccountsBalance(['706'], $endDate, 'credit', [], $startDate),
            'autres_produits' => $this->getAccountsBalance(['70', '71', '72', '73', '74', '75', '76'], $endDate, 'credit', ['701', '702', '706', '707'], $startDate),
        ];

        $revenues['total_produits_exploitation'] = array_sum($revenues);

        // Charges (Classe 6)
        $expenses = [
            'achats_marchandises' => $this->getAccountsBalance(['607'], $endDate, 'debit', [], $startDate),
            'achats_matieres' => $this->getAccountsBalance(['601', '602'], $endDate, 'debit', [], $startDate),
            'services_exterieurs' => $this->getAccountsBalance(['61', '62'], $endDate, 'debit', [], $startDate),
            'impots_taxes' => $this->getAccountsBalance(['63'], $endDate, 'debit', [], $startDate),
            'charges_personnel' => $this->getAccountsBalance(['64'], $endDate, 'debit', [], $startDate),
            'charges_financieres' => $this->getAccountsBalance(['65'], $endDate, 'debit', [], $startDate),
            'dotations_amortissements' => $this->getAccountsBalance(['68'], $endDate, 'debit', [], $startDate),
            'autres_charges' => $this->getAccountsBalance(['60', '66', '67'], $endDate, 'debit', ['601', '602', '607'], $startDate),
        ];

        $expenses['total_charges_exploitation'] = array_sum($expenses);

        // Résultats
        $results = [
            'resultat_exploitation' => $revenues['total_produits_exploitation'] - $expenses['total_charges_exploitation'],
            'resultat_financier' => 0, // Simplifié
            'resultat_exceptionnel' => 0, // Simplifié
        ];

        $results['resultat_avant_impot'] = $results['resultat_exploitation']
            + $results['resultat_financier']
            + $results['resultat_exceptionnel'];

        // Impôt sur les sociétés (estimation 25%)
        $results['impot_societes'] = $results['resultat_avant_impot'] > 0
            ? $results['resultat_avant_impot'] * 0.25
            : 0;

        $results['resultat_net'] = $results['resultat_avant_impot'] - $results['impot_societes'];

        // Marges
        $margins = [
            'marge_brute' => $revenues['ventes_marchandises'] - $expenses['achats_marchandises'],
            'marge_brute_pct' => $revenues['ventes_marchandises'] > 0
                ? (($revenues['ventes_marchandises'] - $expenses['achats_marchandises']) / $revenues['ventes_marchandises']) * 100
                : 0,
            'marge_nette_pct' => $revenues['total_produits_exploitation'] > 0
                ? ($results['resultat_net'] / $revenues['total_produits_exploitation']) * 100
                : 0,
        ];

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'produits' => $revenues,
            'charges' => $expenses,
            'resultats' => $results,
            'marges' => $margins,
        ];
    }

    /**
     * Génère le tableau de flux de trésorerie
     */
    public function generateCashFlowStatement(Carbon $startDate, Carbon $endDate): array
    {
        $incomeStatement = $this->generateIncomeStatement($startDate, $endDate);

        // Flux d'exploitation
        $operatingCashFlow = [
            'resultat_net' => $incomeStatement['resultats']['resultat_net'],
            'dotations_amortissements' => $incomeStatement['charges']['dotations_amortissements'],
            'variation_clients' => $this->getAccountVariation(['411'], $startDate, $endDate) * -1,
            'variation_stocks' => $this->getAccountVariation(['3'], $startDate, $endDate) * -1,
            'variation_fournisseurs' => $this->getAccountVariation(['401'], $startDate, $endDate),
        ];

        $operatingCashFlow['flux_tresorerie_exploitation'] = array_sum($operatingCashFlow);

        // Flux d'investissement
        $investmentCashFlow = [
            'acquisitions_immobilisations' => $this->getAccountVariation(['2'], $startDate, $endDate, 'debit') * -1,
            'cessions_immobilisations' => 0, // Simplifié
        ];

        $investmentCashFlow['flux_tresorerie_investissement'] = array_sum($investmentCashFlow);

        // Flux de financement
        $financingCashFlow = [
            'augmentation_capital' => $this->getAccountVariation(['101'], $startDate, $endDate, 'credit'),
            'nouveaux_emprunts' => $this->getAccountVariation(['16'], $startDate, $endDate, 'credit'),
            'remboursements_emprunts' => $this->getAccountVariation(['16'], $startDate, $endDate, 'debit') * -1,
        ];

        $financingCashFlow['flux_tresorerie_financement'] = array_sum($financingCashFlow);

        // Variation totale de trésorerie
        $totalCashFlow = $operatingCashFlow['flux_tresorerie_exploitation']
            + $investmentCashFlow['flux_tresorerie_investissement']
            + $financingCashFlow['flux_tresorerie_financement'];

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'flux_exploitation' => $operatingCashFlow,
            'flux_investissement' => $investmentCashFlow,
            'flux_financement' => $financingCashFlow,
            'variation_tresorerie' => $totalCashFlow,
        ];
    }

    /**
     * Calcule les ratios financiers
     */
    public function calculateFinancialRatios(Carbon $asOfDate): array
    {
        $balanceSheet = $this->generateBalanceSheet($asOfDate);
        $incomeStatement = $this->generateIncomeStatement(
            $asOfDate->copy()->startOfYear(),
            $asOfDate
        );

        $actif = $balanceSheet['actif'];
        $passif = $balanceSheet['passif'];
        $produits = $incomeStatement['produits'];
        $charges = $incomeStatement['charges'];

        return [
            // Ratios de liquidité
            'liquidite_generale' => $actif['total_actif_circulant'] > 0 && $passif['total_dettes'] > 0
                ? $actif['total_actif_circulant'] / $passif['total_dettes']
                : 0,
            'liquidite_reduite' => $passif['total_dettes'] > 0
                ? ($actif['creances_clients'] + $actif['tresorerie_actif']) / $passif['total_dettes']
                : 0,

            // Ratios de solvabilité
            'autonomie_financiere' => $actif['total_actif'] > 0
                ? $passif['total_capitaux_propres'] / $actif['total_actif']
                : 0,
            'endettement' => $passif['total_capitaux_propres'] > 0
                ? $passif['total_dettes'] / $passif['total_capitaux_propres']
                : 0,

            // Ratios de rentabilité
            'rentabilite_economique' => $actif['total_actif'] > 0
                ? ($incomeStatement['resultats']['resultat_net'] / $actif['total_actif']) * 100
                : 0,
            'rentabilite_financiere' => $passif['total_capitaux_propres'] > 0
                ? ($incomeStatement['resultats']['resultat_net'] / $passif['total_capitaux_propres']) * 100
                : 0,

            // Ratios d'activité
            'rotation_clients' => $produits['total_produits_exploitation'] > 0 && $actif['creances_clients'] > 0
                ? ($actif['creances_clients'] / $produits['total_produits_exploitation']) * 365
                : 0,
            'rotation_fournisseurs' => $charges['total_charges_exploitation'] > 0 && $passif['dettes_fournisseurs'] > 0
                ? ($passif['dettes_fournisseurs'] / $charges['total_charges_exploitation']) * 365
                : 0,
        ];
    }

    /**
     * Génère une analyse financière avec l'IA
     */
    public function generateAIAnalysis(Carbon $asOfDate): array
    {
        $reports = $this->generateAllFinancialReports($asOfDate->year, $asOfDate->month);

        $prompt = "Tu es un expert-comptable et analyste financier tunisien. Analyse ces états financiers et fournis:

1. Une synthèse de la situation financière
2. Les points forts (3-5 points)
3. Les points faibles (3-5 points)
4. Les risques identifiés
5. Des recommandations concrètes (5-7 actions)

Données financières:
" . json_encode($reports, JSON_PRETTY_PRINT) . "

Réponds en JSON structuré en français.";

        try {
            $response = $this->anthropic->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 4096,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            $content = $response->content[0]->text;

            // Extraire le JSON de la réponse
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $analysis = json_decode($matches[0], true);
                if ($analysis) {
                    return $analysis;
                }
            }

            return $this->getDefaultAnalysis($reports);
        } catch (\Exception $e) {
            Log::error('AI Analysis Error: ' . $e->getMessage());
            return $this->getDefaultAnalysis($reports);
        }
    }

    /**
     * Analyse par défaut si l'IA échoue
     */
    private function getDefaultAnalysis(array $reports): array
    {
        $incomeStatement = $reports['income_statement'];
        $balanceSheet = $reports['balance_sheet'];

        return [
            'synthese' => 'Analyse automatique des états financiers',
            'points_forts' => [
                $incomeStatement['resultats']['resultat_net'] > 0
                    ? 'Résultat net positif'
                    : 'En cours d\'analyse',
            ],
            'points_faibles' => [],
            'risques' => [],
            'recommandations' => [
                'Optimiser la gestion de trésorerie',
                'Surveiller les créances clients',
                'Réduire les coûts opérationnels',
            ]
        ];
    }

    /**
     * Récupère le solde des comptes
     */
    private function getAccountsBalance(
        array $accountPrefixes,
        Carbon $asOfDate,
        string $type = 'debit',
        array $excludePrefixes = [],
        ?Carbon $startDate = null
    ): float {
        $query = JournalLine::whereHas('journalEntry', function ($q) use ($asOfDate, $startDate) {
            $q->where('company_id', $this->companyId)
              ->where('is_validated', true)
              ->where('date', '<=', $asOfDate);

            if ($startDate) {
                $q->where('date', '>=', $startDate);
            }
        })->whereHas('account', function ($q) use ($accountPrefixes, $excludePrefixes) {
            $q->where(function ($query) use ($accountPrefixes) {
                foreach ($accountPrefixes as $prefix) {
                    $query->orWhere('code', 'LIKE', $prefix . '%');
                }
            });

            if (!empty($excludePrefixes)) {
                foreach ($excludePrefixes as $exclude) {
                    $q->where('code', 'NOT LIKE', $exclude . '%');
                }
            }
        });

        $debit = (clone $query)->sum('debit');
        $credit = (clone $query)->sum('credit');

        return $type === 'debit' ? ($debit - $credit) : ($credit - $debit);
    }

    /**
     * Calcule la variation d'un compte entre deux dates
     */
    private function getAccountVariation(
        array $accountPrefixes,
        Carbon $startDate,
        Carbon $endDate,
        ?string $type = null
    ): float {
        $startBalance = $this->getAccountsBalance($accountPrefixes, $startDate, $type ?? 'debit');
        $endBalance = $this->getAccountsBalance($accountPrefixes, $endDate, $type ?? 'debit');

        return $endBalance - $startBalance;
    }

    /**
     * Calcule le résultat net
     */
    private function calculateNetIncome(Carbon $asOfDate): float
    {
        $incomeStatement = $this->generateIncomeStatement(
            $asOfDate->copy()->startOfYear(),
            $asOfDate
        );

        return $incomeStatement['resultats']['resultat_net'];
    }
}
