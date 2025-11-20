<?php

namespace App\Services\Belgium;

use App\Models\Company;
use App\Models\Belgium\VATDeclarationBE;
use App\Models\Belgium\PayrollBE;
use App\Models\Belgium\CompanyTaxBE;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Service d'automatisation des déclarations fiscales Belgique
 * - TVA (mensuelle/trimestrielle)
 * - ONSS (trimestrielle)
 * - Impôt des sociétés (annuel)
 */
class AutoTaxServiceBE
{
    protected string $claudeApiKey;
    protected string $claudeModel = 'claude-sonnet-4-20250514';

    public function __construct()
    {
        $this->claudeApiKey = config('services.anthropic.api_key');
    }

    /**
     * Générer automatiquement la déclaration TVA
     */
    public function generateVATDeclaration(
        Company $company,
        int $year,
        int $month,
        string $periodType = VATDeclarationBE::PERIOD_MONTHLY
    ): array {
        try {
            // 1. Calculer la TVA
            $vatData = VATDeclarationBE::calculate($company, $year, $month, $periodType);

            // 2. Créer la déclaration
            $declaration = VATDeclarationBE::create($vatData);

            // 3. Analyser avec IA
            $aiAnalysis = $this->analyzeVATWithAI($vatData);

            // 4. Générer l'écriture comptable
            $journalEntry = $declaration->generateJournalEntry();

            return [
                'success' => true,
                'declaration' => $declaration,
                'journal_entry' => $journalEntry,
                'ai_analysis' => $aiAnalysis,
                'payment_reference' => $declaration->payment_reference,
                'deadline' => $declaration->deadline,
            ];

        } catch (\Exception $e) {
            Log::error('Error generating VAT declaration BE: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Analyser la déclaration TVA avec IA
     */
    protected function analyzeVATWithAI(array $vatData): array
    {
        $prompt = "Tu es un expert fiscal belge. Analyse cette déclaration TVA et fournis des recommandations.

Données TVA:
- Ventes 21%: {$vatData['sales_21']}€
- TVA collectée 21%: {$vatData['vat_21']}€
- Ventes 12%: {$vatData['sales_12']}€
- TVA collectée 12%: {$vatData['vat_12']}€
- Ventes 6%: {$vatData['sales_6']}€
- TVA collectée 6%: {$vatData['vat_6']}€
- Achats: {$vatData['purchases_domestic']}€
- TVA déductible: {$vatData['vat_deductible']}€
- TVA nette: " . ($vatData['vat_to_pay'] > 0 ? $vatData['vat_to_pay'] : -$vatData['vat_to_recover']) . "€

Analyse:
1. La déclaration est-elle cohérente?
2. Le ratio TVA collectée/déductible est-il normal pour ce secteur?
3. Y a-t-il des anomalies?
4. Recommandations pour optimiser?

Réponds en JSON:
{
    \"is_coherent\": true/false,
    \"coherence_score\": 0-100,
    \"anomalies\": [\"anomalie1\", \"anomalie2\"],
    \"recommendations\": [\"recommandation1\", \"recommandation2\"],
    \"risk_level\": \"low/medium/high\",
    \"tax_optimization_tips\": [\"conseil1\", \"conseil2\"]
}";

        $response = Http::withHeaders([
            'x-api-key' => $this->claudeApiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->claudeModel,
            'max_tokens' => 1500,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $content = $result['content'][0]['text'];

            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                return json_decode($matches[0], true);
            }
        }

        return [
            'is_coherent' => true,
            'coherence_score' => 80,
            'anomalies' => [],
            'recommendations' => [],
            'risk_level' => 'low'
        ];
    }

    /**
     * Générer automatiquement la déclaration ONSS
     */
    public function generateONSSDeclaration(Company $company, int $year, int $quarter): array
    {
        try {
            // Récupérer toutes les paies du trimestre
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $quarter * 3;

            $payrolls = PayrollBE::where('company_id', $company->id)
                ->where('year', $year)
                ->whereBetween('month', [$startMonth, $endMonth])
                ->get();

            // Calculer totaux
            $totalGrossSalaries = $payrolls->sum('gross_salary');
            $totalOnssEmployee = $payrolls->sum('onss_employee');
            $totalOnssEmployer = $payrolls->sum('onss_employer');
            $totalOnss = $totalOnssEmployee + $totalOnssEmployer;

            // Détails par employé
            $employeeDetails = $payrolls->groupBy('employee_id')->map(function ($group) {
                return [
                    'employee_id' => $group->first()->employee_id,
                    'employee_name' => $group->first()->employee->full_name ?? 'N/A',
                    'niss' => $group->first()->employee->niss ?? 'N/A',
                    'total_gross' => $group->sum('gross_salary'),
                    'total_onss_employee' => $group->sum('onss_employee'),
                    'total_onss_employer' => $group->sum('onss_employer'),
                ];
            })->values();

            return [
                'success' => true,
                'year' => $year,
                'quarter' => $quarter,
                'period' => "Q{$quarter} {$year}",
                'total_gross_salaries' => $totalGrossSalaries,
                'total_onss_employee' => $totalOnssEmployee,
                'total_onss_employer' => $totalOnssEmployer,
                'total_onss' => $totalOnss,
                'employee_count' => $payrolls->pluck('employee_id')->unique()->count(),
                'employee_details' => $employeeDetails,
                'payment_deadline' => $this->getONSSDeadline($year, $quarter),
            ];

        } catch (\Exception $e) {
            Log::error('Error generating ONSS declaration BE: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtenir la date limite ONSS
     * Belgique: Dernier jour du mois suivant le trimestre
     */
    protected function getONSSDeadline(int $year, int $quarter): string
    {
        $lastMonthOfQuarter = $quarter * 3;
        return \Carbon\Carbon::create($year, $lastMonthOfQuarter, 1)
            ->addMonth()
            ->endOfMonth()
            ->format('Y-m-d');
    }

    /**
     * Générer automatiquement la déclaration d'impôt des sociétés
     */
    public function generateCompanyTaxDeclaration(
        Company $company,
        int $fiscalYear,
        ?array $additionalData = null
    ): array {
        try {
            // 1. Calculer le résultat comptable
            $accountingProfit = $this->calculateAccountingProfit($company, $fiscalYear);

            // 2. Récupérer/définir les ajustements
            $additionalData = $additionalData ?? $this->getDefaultAdjustments($company, $fiscalYear);

            // 3. Calculer l'IS
            $taxData = CompanyTaxBE::calculate($company, $fiscalYear, $accountingProfit, $additionalData);

            // 4. Créer la déclaration
            $declaration = CompanyTaxBE::create($taxData);

            // 5. Analyser avec IA
            $aiAnalysis = $this->analyzeCompanyTaxWithAI($taxData);

            // 6. Générer l'écriture comptable
            $journalEntry = $declaration->generateJournalEntry();

            return [
                'success' => true,
                'declaration' => $declaration,
                'journal_entry' => $journalEntry,
                'ai_analysis' => $aiAnalysis,
                'effective_rate' => $declaration->effective_tax_rate,
                'filing_deadline' => $declaration->filing_deadline,
            ];

        } catch (\Exception $e) {
            Log::error('Error generating company tax declaration BE: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculer le résultat comptable
     */
    protected function calculateAccountingProfit(Company $company, int $fiscalYear): float
    {
        // Récupérer les produits (classe 7)
        $revenues = DB::table('journal_entries as je')
            ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
            ->join('belgium_chart_of_accounts as acc', 'jl.account_number', '=', 'acc.account_number')
            ->where('je.company_id', $company->id)
            ->whereYear('je.date', $fiscalYear)
            ->where('acc.class', '7')
            ->sum(DB::raw('jl.credit - jl.debit'));

        // Récupérer les charges (classe 6)
        $expenses = DB::table('journal_entries as je')
            ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
            ->join('belgium_chart_of_accounts as acc', 'jl.account_number', '=', 'acc.account_number')
            ->where('je.company_id', $company->id)
            ->whereYear('je.date', $fiscalYear)
            ->where('acc.class', '6')
            ->sum(DB::raw('jl.debit - jl.credit'));

        return $revenues - $expenses;
    }

    /**
     * Obtenir les ajustements fiscaux par défaut
     */
    protected function getDefaultAdjustments(Company $company, int $fiscalYear): array
    {
        // Calcul déduction intérêts notionnels
        $equity = $this->getEquity($company, $fiscalYear);
        $notionalInterest = CompanyTaxBE::calculateNotionalInterestDeduction($equity);

        return [
            'deductible_expenses' => 0,
            'non_deductible_expenses' => 0,
            'tax_exempt_income' => 0,
            'notional_interest_deduction' => $notionalInterest,
            'investment_deduction' => 0,
            'prepayments' => 0,
        ];
    }

    /**
     * Obtenir les fonds propres
     */
    protected function getEquity(Company $company, int $fiscalYear): float
    {
        return DB::table('journal_entries as je')
            ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
            ->join('belgium_chart_of_accounts as acc', 'jl.account_number', '=', 'acc.account_number')
            ->where('je.company_id', $company->id)
            ->whereYear('je.date', '<=', $fiscalYear)
            ->where('acc.class', '1')
            ->where('acc.type', 'equity')
            ->sum(DB::raw('jl.credit - jl.debit'));
    }

    /**
     * Analyser l'IS avec IA
     */
    protected function analyzeCompanyTaxWithAI(array $taxData): array
    {
        $prompt = "Tu es un expert fiscal belge. Analyse cette situation fiscale et propose des optimisations.

Données:
- Résultat comptable: {$taxData['accounting_profit']}€
- Résultat fiscal: {$taxData['taxable_profit']}€
- Impôt: {$taxData['tax_amount']}€
- Taux effectif: {$taxData['details']['effective_rate']}%
- PME: " . ($taxData['is_sme'] ? 'Oui' : 'Non') . "
- Déduction intérêts notionnels: {$taxData['notional_interest_deduction']}€

Analyse et recommande:
1. Optimisations fiscales possibles
2. Utilisation déductions (intérêts notionnels, investissements)
3. Stratégies pour réduire la charge fiscale légalement

Réponds en JSON:
{
    \"optimization_score\": 0-100,
    \"potential_savings\": 0,
    \"recommendations\": [
        {
            \"title\": \"titre\",
            \"description\": \"description\",
            \"estimated_savings\": 0,
            \"difficulty\": \"easy/medium/hard\"
        }
    ],
    \"warnings\": [\"avertissement1\"],
    \"next_steps\": [\"étape1\", \"étape2\"]
}";

        $response = Http::withHeaders([
            'x-api-key' => $this->claudeApiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->claudeModel,
            'max_tokens' => 2000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $content = $result['content'][0]['text'];

            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                return json_decode($matches[0], true);
            }
        }

        return [
            'optimization_score' => 70,
            'potential_savings' => 0,
            'recommendations' => [],
            'warnings' => [],
            'next_steps' => []
        ];
    }

    /**
     * Vérifier les échéances fiscales
     */
    public function checkDeadlines(Company $company, int $year, int $month): array
    {
        $deadlines = [];

        // Vérifier TVA
        $vatDeclarations = VATDeclarationBE::where('company_id', $company->id)
            ->where('year', $year)
            ->where('status', '!=', VATDeclarationBE::STATUS_SUBMITTED)
            ->get();

        foreach ($vatDeclarations as $vat) {
            if ($vat->isOverdue()) {
                $deadlines[] = [
                    'type' => 'vat',
                    'description' => "Déclaration TVA {$vat->period_name}",
                    'deadline' => $vat->deadline,
                    'days_overdue' => now()->diffInDays($vat->deadline, false),
                    'amount' => $vat->vat_to_pay,
                    'status' => 'overdue',
                ];
            }
        }

        // Vérifier IS
        $companyTaxes = CompanyTaxBE::where('company_id', $company->id)
            ->where('fiscal_year', $year)
            ->where('status', '!=', CompanyTaxBE::STATUS_FILED)
            ->get();

        foreach ($companyTaxes as $tax) {
            if ($tax->isOverdue()) {
                $deadlines[] = [
                    'type' => 'company_tax',
                    'description' => "Impôt des sociétés {$tax->fiscal_year}",
                    'deadline' => $tax->filing_deadline,
                    'days_overdue' => now()->diffInDays($tax->filing_deadline, false),
                    'amount' => $tax->tax_to_pay,
                    'status' => 'overdue',
                ];
            }
        }

        return $deadlines;
    }
}
