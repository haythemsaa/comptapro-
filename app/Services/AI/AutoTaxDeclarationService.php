<?php

namespace App\Services\AI;

use App\Models\Tax\VATDeclarationTunisia;
use App\Models\Tax\CorporateTaxDeclaration;
use App\Models\Payroll\CNSSDeclaration;
use App\Services\Tax\TunisianVATCalculator;
use App\Services\Payroll\TunisianPayrollCalculator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service d'automatisation des déclarations fiscales avec IA
 * Génère automatiquement toutes les déclarations (TVA, IS, CNSS, etc.)
 */
class AutoTaxDeclarationService
{
    private int $companyId;
    private TunisianVATCalculator $vatCalculator;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
        $this->vatCalculator = new TunisianVATCalculator();
    }

    /**
     * Génère automatiquement toutes les déclarations pour une période
     */
    public function generateAllDeclarations(int $year, int $month): array
    {
        $results = [
            'vat_declaration' => null,
            'cnss_declaration' => null,
            'payroll_summary' => null,
            'errors' => []
        ];

        try {
            // Déclaration TVA
            $results['vat_declaration'] = $this->generateVATDeclaration($year, $month);

            // Déclaration CNSS
            $results['cnss_declaration'] = $this->generateCNSSDeclaration($year, $month);

            // Résumé de paie
            $results['payroll_summary'] = $this->generatePayrollSummary($year, $month);

            Log::info("Auto-generated all declarations for {$year}-{$month}");

            return $results;
        } catch (\Exception $e) {
            Log::error('Auto Declaration Error: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
            return $results;
        }
    }

    /**
     * Génère automatiquement la déclaration TVA
     */
    public function generateVATDeclaration(int $year, int $month): VATDeclarationTunisia
    {
        try {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            // Calculer la TVA automatiquement
            $calculation = $this->vatCalculator->calculateVATDeclaration(
                $this->companyId,
                $startDate,
                $endDate
            );

            // Créer la déclaration
            $declaration = VATDeclarationTunisia::create(array_merge([
                'company_id' => $this->companyId,
                'period_month' => $month,
                'period_year' => $year,
                'status' => 'draft',
                'generated_by_ai' => true,
                'due_date' => $endDate->copy()->endOfMonth()->addDay(),
            ], $calculation));

            Log::info("VAT declaration auto-generated for {$year}-{$month}");

            return $declaration;
        } catch (\Exception $e) {
            Log::error("VAT auto-generation error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Génère automatiquement la déclaration CNSS
     */
    public function generateCNSSDeclaration(int $year, int $month): CNSSDeclaration
    {
        try {
            // Récupérer tous les bulletins de paie du mois
            $payslips = \App\Models\Payroll\Payslip::where('company_id', $this->companyId)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->get();

            if ($payslips->isEmpty()) {
                throw new \Exception("Aucun bulletin de paie trouvé pour {$year}-{$month}");
            }

            // Calculer les totaux
            $employeeCount = $payslips->count();
            $totalGrossSalary = $payslips->sum('gross_salary');
            $totalCNSSEmployee = $payslips->sum('cnss_employee');
            $totalCNSSEmployer = $payslips->sum('cnss_employer');
            $totalCNSS = $totalCNSSEmployee + $totalCNSSEmployer;

            // Créer la déclaration CNSS
            $declaration = CNSSDeclaration::create([
                'company_id' => $this->companyId,
                'period_month' => $month,
                'period_year' => $year,
                'employee_count' => $employeeCount,
                'total_gross_salary' => $totalGrossSalary,
                'total_cnss_employee' => $totalCNSSEmployee,
                'total_cnss_employer' => $totalCNSSEmployer,
                'total_cnss' => $totalCNSS,
                'due_date' => Carbon::create($year, $month, 1)->addMonth()->day(15),
                'status' => 'draft',
                'generated_by_ai' => true,
            ]);

            Log::info("CNSS declaration auto-generated for {$year}-{$month}");

            return $declaration;
        } catch (\Exception $e) {
            Log::error("CNSS auto-generation error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Génère automatiquement le résumé de paie
     */
    public function generatePayrollSummary(int $year, int $month): array
    {
        $payslips = \App\Models\Payroll\Payslip::where('company_id', $this->companyId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->with('employee')
            ->get();

        if ($payslips->isEmpty()) {
            return [
                'period' => "{$year}-{$month}",
                'employee_count' => 0,
                'total_base_salary' => 0,
                'total_gross_salary' => 0,
                'total_net_salary' => 0,
                'total_cnss_employee' => 0,
                'total_cnss_employer' => 0,
                'total_irpp' => 0,
                'total_cost_company' => 0,
            ];
        }

        return [
            'period' => "{$year}-{$month}",
            'employee_count' => $payslips->count(),
            'total_base_salary' => round($payslips->sum('base_salary'), 3),
            'total_gross_salary' => round($payslips->sum('gross_salary'), 3),
            'total_net_salary' => round($payslips->sum('net_salary'), 3),
            'total_cnss_employee' => round($payslips->sum('cnss_employee'), 3),
            'total_cnss_employer' => round($payslips->sum('cnss_employer'), 3),
            'total_irpp' => round($payslips->sum('irpp'), 3),
            'total_cost_company' => round($payslips->sum('total_cost_company'), 3),
            'payslips' => $payslips->map(function ($payslip) {
                return [
                    'employee' => $payslip->employee->full_name,
                    'net_salary' => $payslip->net_salary,
                    'total_cost' => $payslip->total_cost_company,
                ];
            })->toArray(),
        ];
    }

    /**
     * Génère automatiquement la déclaration IS (annuelle)
     */
    public function generateCorporateTaxDeclaration(int $fiscalYear): CorporateTaxDeclaration
    {
        try {
            // Récupérer le résultat comptable via AutoFinancialReportsService
            $financialReports = new AutoFinancialReportsService($this->companyId);
            $incomeStatement = $financialReports->generateIncomeStatement(
                Carbon::create($fiscalYear, 1, 1),
                Carbon::create($fiscalYear, 12, 31)
            );

            $accountingResult = $incomeStatement['resultats']['resultat_avant_impot'];

            // Réintégrations et déductions automatiques (estimation)
            $reintegrations = $this->calculateReintegrations($fiscalYear);
            $deductions = $this->calculateDeductions($fiscalYear);

            $fiscalResult = $accountingResult + $reintegrations - $deductions;
            $taxableBase = max(0, $fiscalResult);

            // Déterminer le taux d'IS (par défaut 25%)
            $taxRate = 25.00;

            $corporateTaxDue = round($taxableBase * ($taxRate / 100), 3);

            // Récupérer les acomptes payés
            $advancesPaid = $this->getAdvancesPaid($fiscalYear);

            $balanceToPay = $corporateTaxDue - $advancesPaid;

            // Créer la déclaration IS
            $declaration = CorporateTaxDeclaration::create([
                'company_id' => $this->companyId,
                'fiscal_year' => $fiscalYear,
                'accounting_result' => $accountingResult,
                'reintegrations' => $reintegrations,
                'deductions' => $deductions,
                'fiscal_result' => $fiscalResult,
                'tax_loss_carryforward' => 0,
                'taxable_base' => $taxableBase,
                'tax_rate' => $taxRate,
                'corporate_tax_due' => $corporateTaxDue,
                'advances_paid' => $advancesPaid,
                'balance_to_pay' => $balanceToPay,
                'due_date' => Carbon::create($fiscalYear + 1, 3, 25),
                'status' => 'draft',
                'generated_by_ai' => true,
            ]);

            Log::info("Corporate tax declaration auto-generated for {$fiscalYear}");

            return $declaration;
        } catch (\Exception $e) {
            Log::error("Corporate tax auto-generation error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calcule les réintégrations automatiquement
     */
    private function calculateReintegrations(int $year): float
    {
        // Réintégrations courantes en Tunisie:
        // - Amendes et pénalités fiscales (compte 671)
        // - Charges non déductibles

        $reintegrations = 0;

        // Amendes et pénalités (non déductibles)
        $penalties = \App\Models\JournalLine::whereHas('journalEntry', function ($q) use ($year) {
            $q->where('company_id', $this->companyId)
              ->whereYear('date', $year)
              ->where('is_validated', true);
        })->whereHas('account', function ($q) {
            $q->where('code', 'LIKE', '671%');
        })->sum('debit');

        $reintegrations += $penalties;

        return round($reintegrations, 3);
    }

    /**
     * Calcule les déductions automatiquement
     */
    private function calculateDeductions(int $year): float
    {
        // Déductions courantes en Tunisie:
        // - Revenus exonérés
        // - Certains investissements

        $deductions = 0;

        // À implémenter selon les besoins spécifiques

        return round($deductions, 3);
    }

    /**
     * Récupère les acomptes IS payés
     */
    private function getAdvancesPaid(int $year): float
    {
        // Rechercher les paiements d'acomptes (compte 442)
        $advances = \App\Models\JournalLine::whereHas('journalEntry', function ($q) use ($year) {
            $q->where('company_id', $this->companyId)
              ->whereYear('date', $year)
              ->where('is_validated', true);
        })->whereHas('account', function ($q) {
            $q->where('code', 'LIKE', '442%');
        })->sum('debit');

        return round($advances, 3);
    }

    /**
     * Génère automatiquement toutes les déclarations de l'année
     */
    public function generateAnnualDeclarations(int $year): array
    {
        $results = [
            'monthly_declarations' => [],
            'annual_declarations' => [],
            'errors' => []
        ];

        try {
            // Générer les déclarations mensuelles (TVA, CNSS)
            for ($month = 1; $month <= 12; $month++) {
                try {
                    $monthlyResults = $this->generateAllDeclarations($year, $month);
                    $results['monthly_declarations'][$month] = $monthlyResults;
                } catch (\Exception $e) {
                    $results['errors'][] = "Month {$month}: " . $e->getMessage();
                }
            }

            // Générer la déclaration IS annuelle
            try {
                $results['annual_declarations']['corporate_tax'] = $this->generateCorporateTaxDeclaration($year);
            } catch (\Exception $e) {
                $results['errors'][] = "Corporate Tax: " . $e->getMessage();
            }

            Log::info("Generated all annual declarations for {$year}");

            return $results;
        } catch (\Exception $e) {
            Log::error("Annual declarations error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifie et alerte sur les déclarations à venir
     */
    public function checkUpcomingDeadlines(int $daysAhead = 7): array
    {
        $today = Carbon::today();
        $futureDate = $today->copy()->addDays($daysAhead);

        $upcoming = [
            'vat' => VATDeclarationTunisia::where('company_id', $this->companyId)
                ->where('status', 'draft')
                ->whereBetween('due_date', [$today, $futureDate])
                ->get(),
            'cnss' => CNSSDeclaration::where('company_id', $this->companyId)
                ->where('status', 'draft')
                ->whereBetween('due_date', [$today, $futureDate])
                ->get(),
            'corporate_tax' => CorporateTaxDeclaration::where('company_id', $this->companyId)
                ->where('status', 'draft')
                ->whereBetween('due_date', [$today, $futureDate])
                ->get(),
        ];

        $alerts = [];

        foreach ($upcoming['vat'] as $declaration) {
            $daysLeft = $today->diffInDays($declaration->due_date);
            $alerts[] = [
                'type' => 'TVA',
                'period' => "{$declaration->period_year}-{$declaration->period_month}",
                'due_date' => $declaration->due_date->format('Y-m-d'),
                'days_left' => $daysLeft,
                'severity' => $daysLeft <= 3 ? 'high' : 'medium',
                'message' => "Déclaration TVA à soumettre dans {$daysLeft} jours"
            ];
        }

        foreach ($upcoming['cnss'] as $declaration) {
            $daysLeft = $today->diffInDays($declaration->due_date);
            $alerts[] = [
                'type' => 'CNSS',
                'period' => "{$declaration->period_year}-{$declaration->period_month}",
                'due_date' => $declaration->due_date->format('Y-m-d'),
                'days_left' => $daysLeft,
                'severity' => $daysLeft <= 3 ? 'high' : 'medium',
                'message' => "Déclaration CNSS à soumettre dans {$daysLeft} jours"
            ];
        }

        foreach ($upcoming['corporate_tax'] as $declaration) {
            $daysLeft = $today->diffInDays($declaration->due_date);
            $alerts[] = [
                'type' => 'IS',
                'period' => $declaration->fiscal_year,
                'due_date' => $declaration->due_date->format('Y-m-d'),
                'days_left' => $daysLeft,
                'severity' => $daysLeft <= 7 ? 'high' : 'medium',
                'message' => "Déclaration IS à soumettre dans {$daysLeft} jours"
            ];
        }

        return [
            'count' => count($alerts),
            'alerts' => $alerts
        ];
    }
}
