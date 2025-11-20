<?php

namespace App\Services\Payroll;

use App\Models\Payroll\Employee;
use App\Models\Payroll\PayrollSettings;
use Illuminate\Support\Facades\Log;

/**
 * Calculateur de paie conforme à la législation tunisienne 2024
 *
 * Références:
 * - Code du Travail tunisien
 * - Loi de finances 2024
 * - Taux CNSS 2024
 */
class TunisianPayrollCalculator
{
    private PayrollSettings $settings;
    private array $calculationDetails = [];

    // Barème IRPP 2024 pour salariés (Article 44 du Code IRPP)
    private const IRPP_BRACKETS = [
        ['min' => 0, 'max' => 5000, 'rate' => 0],
        ['min' => 5000.001, 'max' => 20000, 'rate' => 26],
        ['min' => 20000.001, 'max' => 30000, 'rate' => 28],
        ['min' => 30000.001, 'max' => 50000, 'rate' => 32],
        ['min' => 50000.001, 'max' => PHP_FLOAT_MAX, 'rate' => 35],
    ];

    // Déductions familiales (Article 40 bis du Code IRPP)
    private const FAMILY_DEDUCTIONS = [
        'married' => 150, // Chef de famille
        'child_1' => 100, // 1er enfant
        'child_2' => 90,  // 2ème enfant
        'child_3' => 80,  // 3ème enfant
        'child_4' => 60,  // 4ème enfant et plus (chacun)
        'parent' => 150,  // Parents à charge (chacun)
    ];

    public function __construct(?PayrollSettings $settings = null)
    {
        $this->settings = $settings ?? $this->getDefaultSettings();
    }

    /**
     * Calcule un bulletin de paie complet
     */
    public function calculatePayslip(Employee $employee, int $month, int $year, array $extras = []): array
    {
        $this->calculationDetails = [];

        // 1. Calcul du salaire brut
        $baseSalary = $employee->base_salary;
        $seniorityBonus = $this->calculateSeniorityBonus($employee);
        $transportAllowance = $extras['transport_allowance'] ?? 0;
        $foodAllowance = $extras['food_allowance'] ?? 0;
        $housingAllowance = $extras['housing_allowance'] ?? 0;
        $performanceBonus = $extras['performance_bonus'] ?? 0;
        $overtimePay = $extras['overtime_pay'] ?? 0;
        $otherBonuses = $extras['other_bonuses'] ?? 0;

        $grossSalary = $baseSalary + $seniorityBonus + $transportAllowance +
                      $foodAllowance + $housingAllowance + $performanceBonus +
                      $overtimePay + $otherBonuses;

        // 2. Calcul du brut imposable (certains avantages sont exonérés)
        $transportExempt = min($transportAllowance, $this->settings->transport_tax_free);
        $taxableGross = $grossSalary - $transportExempt;

        // 3. Calcul des cotisations CNSS (sur brut plafonné)
        $cnssBase = min($taxableGross, $this->settings->cnss_ceiling);
        $cnssEmployee = round($cnssBase * ($this->settings->cnss_employee_rate / 100), 3);
        $cnssEmployer = round($cnssBase * ($this->settings->cnss_employer_rate / 100), 3);
        $accidentInsurance = round($cnssBase * ($this->settings->accident_insurance_rate / 100), 3);

        // 4. Calcul de l'IRPP
        $irppAmount = $this->calculateIRPP($employee, $taxableGross, $cnssEmployee);

        // 5. Autres retenues
        $advancePayment = $extras['advance_payment'] ?? 0;
        $loanDeduction = $extras['loan_deduction'] ?? 0;
        $otherDeductions = $extras['other_deductions'] ?? 0;

        $totalDeductions = $cnssEmployee + $irppAmount + $advancePayment +
                          $loanDeduction + $otherDeductions;

        // 6. Calcul du net à payer
        $netSalary = $grossSalary - $totalDeductions;
        $netToPay = $netSalary;

        // Stocker les détails du calcul
        $this->calculationDetails = [
            'calculation_date' => now()->toIso8601String(),
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'cnss_number' => $employee->cnss_number,
                'position' => $employee->position,
                'seniority_years' => $employee->seniority_years,
            ],
            'gross_components' => [
                'base_salary' => $baseSalary,
                'seniority_bonus' => $seniorityBonus,
                'seniority_percentage' => $employee->seniority_bonus,
                'transport_allowance' => $transportAllowance,
                'food_allowance' => $foodAllowance,
                'housing_allowance' => $housingAllowance,
                'performance_bonus' => $performanceBonus,
                'overtime_pay' => $overtimePay,
                'other_bonuses' => $otherBonuses,
                'gross_salary' => $grossSalary,
            ],
            'taxable_income' => [
                'taxable_gross' => $taxableGross,
                'transport_exempt' => $transportExempt,
                'cnss_base' => $cnssBase,
                'cnss_ceiling_applied' => $cnssBase >= $this->settings->cnss_ceiling,
            ],
            'social_contributions' => [
                'employee' => [
                    'cnss' => $cnssEmployee,
                    'cnss_rate' => $this->settings->cnss_employee_rate,
                ],
                'employer' => [
                    'cnss' => $cnssEmployer,
                    'cnss_rate' => $this->settings->cnss_employer_rate,
                    'accident_insurance' => $accidentInsurance,
                    'accident_rate' => $this->settings->accident_insurance_rate,
                    'total_employer_charges' => $cnssEmployer + $accidentInsurance,
                ],
            ],
            'irpp_calculation' => $this->calculationDetails['irpp'] ?? [],
            'deductions' => [
                'cnss_employee' => $cnssEmployee,
                'irpp' => $irppAmount,
                'advance_payment' => $advancePayment,
                'loan_deduction' => $loanDeduction,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
            ],
            'net_amounts' => [
                'net_salary' => $netSalary,
                'net_to_pay' => $netToPay,
            ],
            'employer_total_cost' => $grossSalary + $cnssEmployer + $accidentInsurance,
        ];

        return [
            'base_salary' => $baseSalary,
            'seniority_bonus' => $seniorityBonus,
            'transport_allowance' => $transportAllowance,
            'food_allowance' => $foodAllowance,
            'housing_allowance' => $housingAllowance,
            'performance_bonus' => $performanceBonus,
            'overtime_pay' => $overtimePay,
            'other_bonuses' => $otherBonuses,
            'gross_salary' => $grossSalary,
            'taxable_gross' => $taxableGross,
            'cnss_employee' => $cnssEmployee,
            'cnss_employer' => $cnssEmployer,
            'accident_insurance' => $accidentInsurance,
            'irpp_amount' => $irppAmount,
            'advance_payment' => $advancePayment,
            'loan_deduction' => $loanDeduction,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'net_to_pay' => $netToPay,
            'calculation_details' => $this->calculationDetails,
        ];
    }

    /**
     * Calcule l'IRPP selon le barème progressif tunisien 2024
     */
    private function calculateIRPP(Employee $employee, float $taxableGross, float $cnssEmployee): float
    {
        // Revenu net imposable annuel
        $annualGross = $taxableGross * 12;
        $annualCnss = $cnssEmployee * 12;
        $annualNetIncome = $annualGross - $annualCnss;

        // Déductions familiales
        $familyDeductions = $this->calculateFamilyDeductions($employee);
        $taxableIncome = $annualNetIncome - $familyDeductions;

        // Appliquer le barème progressif
        $totalTax = 0;
        $previousMax = 0;
        $bracketDetails = [];

        foreach (self::IRPP_BRACKETS as $bracket) {
            if ($taxableIncome <= $bracket['min']) {
                break;
            }

            $taxableInBracket = min($taxableIncome, $bracket['max']) - $bracket['min'];
            $taxInBracket = $taxableInBracket * ($bracket['rate'] / 100);
            $totalTax += $taxInBracket;

            $bracketDetails[] = [
                'bracket' => "{$bracket['min']} - " . ($bracket['max'] == PHP_FLOAT_MAX ? '∞' : $bracket['max']),
                'rate' => $bracket['rate'],
                'taxable_amount' => round($taxableInBracket, 3),
                'tax_amount' => round($taxInBracket, 3),
            ];

            $previousMax = $bracket['max'];
        }

        // IRPP mensuel (arrondi)
        $monthlyIRPP = round($totalTax / 12, 3);

        // Stocker les détails
        $this->calculationDetails['irpp'] = [
            'annual_gross' => $annualGross,
            'annual_cnss' => $annualCnss,
            'annual_net_income' => $annualNetIncome,
            'family_deductions' => $familyDeductions,
            'family_deduction_details' => $this->getFamilyDeductionDetails($employee),
            'taxable_income' => $taxableIncome,
            'bracket_details' => $bracketDetails,
            'annual_tax' => round($totalTax, 3),
            'monthly_tax' => $monthlyIRPP,
        ];

        return max($monthlyIRPP, 0); // L'IRPP ne peut pas être négatif
    }

    /**
     * Calcule les déductions familiales selon l'Article 40 bis
     */
    private function calculateFamilyDeductions(Employee $employee): float
    {
        $deductions = 0;

        // Chef de famille (marié)
        if (in_array($employee->marital_status, ['marié', 'married'])) {
            $deductions += self::FAMILY_DEDUCTIONS['married'];
        }

        // Enfants à charge
        $childrenCount = min($employee->children_count, 4); // Max 4 enfants
        for ($i = 1; $i <= $childrenCount; $i++) {
            if ($i <= 3) {
                $deductions += self::FAMILY_DEDUCTIONS["child_{$i}"];
            } else {
                $deductions += self::FAMILY_DEDUCTIONS['child_4'];
            }
        }

        return $deductions * 12; // Annuel
    }

    /**
     * Détails des déductions familiales
     */
    private function getFamilyDeductionDetails(Employee $employee): array
    {
        $details = [];

        if (in_array($employee->marital_status, ['marié', 'married'])) {
            $details[] = [
                'type' => 'Chef de famille',
                'monthly' => self::FAMILY_DEDUCTIONS['married'],
                'annual' => self::FAMILY_DEDUCTIONS['married'] * 12,
            ];
        }

        $childrenCount = min($employee->children_count, 4);
        for ($i = 1; $i <= $childrenCount; $i++) {
            $monthly = $i <= 3
                ? self::FAMILY_DEDUCTIONS["child_{$i}"]
                : self::FAMILY_DEDUCTIONS['child_4'];

            $details[] = [
                'type' => "Enfant {$i}",
                'monthly' => $monthly,
                'annual' => $monthly * 12,
            ];
        }

        return $details;
    }

    /**
     * Calcule la prime d'ancienneté
     */
    private function calculateSeniorityBonus(Employee $employee): float
    {
        if (!$this->settings->apply_seniority_bonus) {
            return 0;
        }

        return $employee->calculateSeniorityAmount();
    }

    /**
     * Calcule les charges patronales totales
     */
    public function calculateEmployerCharges(float $grossSalary): array
    {
        $cnssBase = min($grossSalary, $this->settings->cnss_ceiling);

        $cnss = round($cnssBase * ($this->settings->cnss_employer_rate / 100), 3);
        $accident = round($cnssBase * ($this->settings->accident_insurance_rate / 100), 3);

        return [
            'cnss_employer' => $cnss,
            'accident_insurance' => $accident,
            'total_charges' => $cnss + $accident,
            'total_cost' => $grossSalary + $cnss + $accident,
        ];
    }

    /**
     * Paramètres par défaut
     */
    private function getDefaultSettings(): PayrollSettings
    {
        $settings = new PayrollSettings();
        $settings->cnss_employee_rate = 9.18;
        $settings->cnss_employer_rate = 16.57;
        $settings->accident_insurance_rate = 0.4;
        $settings->cnss_ceiling = 6000;
        $settings->transport_tax_free = 100;
        $settings->working_days_per_month = 26;
        $settings->working_hours_per_day = 8;
        $settings->apply_transport_bonus = true;
        $settings->apply_seniority_bonus = true;

        return $settings;
    }

    /**
     * Obtient les détails du dernier calcul
     */
    public function getCalculationDetails(): array
    {
        return $this->calculationDetails;
    }

    /**
     * Simule un salaire net depuis un brut
     */
    public function simulateNetFromGross(float $grossSalary, array $employeeData = []): array
    {
        $cnssBase = min($grossSalary, $this->settings->cnss_ceiling);
        $cnssEmployee = round($cnssBase * ($this->settings->cnss_employee_rate / 100), 3);

        // IRPP simplifié (sans déductions familiales pour simulation)
        $annualNet = ($grossSalary - $cnssEmployee) * 12;
        $annualIrpp = $this->calculateSimpleIRPP($annualNet);
        $monthlyIrpp = round($annualIrpp / 12, 3);

        $netSalary = $grossSalary - $cnssEmployee - $monthlyIrpp;

        return [
            'gross_salary' => $grossSalary,
            'cnss_employee' => $cnssEmployee,
            'irpp' => $monthlyIrpp,
            'net_salary' => $netSalary,
            'employer_charges' => $this->calculateEmployerCharges($grossSalary),
        ];
    }

    /**
     * Simule un salaire brut depuis un net
     */
    public function simulateGrossFromNet(float $netSalary): array
    {
        // Calcul itératif pour trouver le brut correspondant
        $grossEstimate = $netSalary / 0.76; // Approximation initiale (~24% de charges)

        for ($i = 0; $i < 10; $i++) {
            $result = $this->simulateNetFromGross($grossEstimate);
            $difference = $netSalary - $result['net_salary'];

            if (abs($difference) < 1) {
                break;
            }

            $grossEstimate += $difference / 0.76;
        }

        return $this->simulateNetFromGross($grossEstimate);
    }

    /**
     * Calcul IRPP simplifié pour simulation
     */
    private function calculateSimpleIRPP(float $annualIncome): float
    {
        $totalTax = 0;

        foreach (self::IRPP_BRACKETS as $bracket) {
            if ($annualIncome <= $bracket['min']) {
                break;
            }

            $taxableInBracket = min($annualIncome, $bracket['max']) - $bracket['min'];
            $totalTax += $taxableInBracket * ($bracket['rate'] / 100);
        }

        return $totalTax;
    }
}
