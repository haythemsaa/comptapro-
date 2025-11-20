<?php

namespace App\Models\Belgium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Company;
use App\Models\Belgium\EmployeeBE;

/**
 * Model pour la paie Belgique avec ONSS
 *
 * Taux ONSS Belgique 2024:
 * - Employé: 13.07% (cotisation personnelle)
 * - Employeur: ~25% (cotisation patronale)
 * - Précompte professionnel: selon barème progressif
 *
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int $year
 * @property int $month
 * @property float $gross_salary
 * @property float $onss_employee
 * @property float $onss_employer
 * @property float $withholding_tax
 * @property float $net_salary
 * @property float $employer_cost
 * @property array $details
 */
class PayrollBE extends Model
{
    use HasFactory;

    protected $table = 'payrolls_be';

    protected $fillable = [
        'company_id',
        'employee_id',
        'year',
        'month',
        'gross_salary',
        'onss_employee',
        'onss_employer',
        'withholding_tax',
        'net_salary',
        'employer_cost',
        'holiday_pay',
        'meal_vouchers',
        'eco_vouchers',
        'transport_allowance',
        'other_benefits',
        'details',
        'status',
        'paid_at',
        'journal_entry_id',
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'onss_employee' => 'decimal:2',
        'onss_employer' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'employer_cost' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'meal_vouchers' => 'decimal:2',
        'eco_vouchers' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'other_benefits' => 'decimal:2',
        'details' => 'array',
        'paid_at' => 'datetime',
        'year' => 'integer',
        'month' => 'integer',
    ];

    /**
     * Taux ONSS Belgique 2024
     */
    const ONSS_EMPLOYEE_RATE = 0.1307; // 13.07%
    const ONSS_EMPLOYER_BASE_RATE = 0.2500; // 25% (base)
    const ONSS_EMPLOYER_AVERAGE_RATE = 0.2700; // 27% (moyenne avec cotisations spéciales)

    /**
     * Statuts
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PAID = 'paid';

    /**
     * Relations
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeBE::class, 'employee_id');
    }

    /**
     * Calculer la paie complète
     */
    public static function calculate(
        EmployeeBE $employee,
        int $year,
        int $month,
        ?array $additionalData = []
    ): array {
        $grossSalary = $employee->gross_monthly_salary;

        // 1. ONSS Employé (13.07%)
        $onssEmployee = round($grossSalary * self::ONSS_EMPLOYEE_RATE, 2);

        // 2. Salaire imposable = Brut - ONSS
        $taxableSalary = $grossSalary - $onssEmployee;

        // 3. Précompte professionnel (selon barème)
        $withholdingTax = self::calculateWithholdingTax(
            $taxableSalary,
            $employee->marital_status,
            $employee->dependents,
            $employee->has_disability
        );

        // 4. Salaire net = Imposable - Précompte
        $netSalary = round($taxableSalary - $withholdingTax, 2);

        // 5. ONSS Employeur (25-27%)
        $onssEmployer = round($grossSalary * self::ONSS_EMPLOYER_AVERAGE_RATE, 2);

        // 6. Coût total employeur
        $employerCost = $grossSalary + $onssEmployer;

        // 7. Avantages
        $mealVouchers = $additionalData['meal_vouchers'] ?? 0;
        $ecoVouchers = $additionalData['eco_vouchers'] ?? 0;
        $transportAllowance = $additionalData['transport_allowance'] ?? 0;
        $otherBenefits = $additionalData['other_benefits'] ?? 0;

        // 8. Pécule de vacances (calculé annuellement, provisonné mensuellement)
        $holidayPayProvision = round($grossSalary * 0.0769, 2); // ~7.69%

        return [
            'gross_salary' => $grossSalary,
            'onss_employee' => $onssEmployee,
            'taxable_salary' => $taxableSalary,
            'withholding_tax' => $withholdingTax,
            'net_salary' => $netSalary,
            'onss_employer' => $onssEmployer,
            'employer_cost' => $employerCost,
            'holiday_pay' => $holidayPayProvision,
            'meal_vouchers' => $mealVouchers,
            'eco_vouchers' => $ecoVouchers,
            'transport_allowance' => $transportAllowance,
            'other_benefits' => $otherBenefits,
            'net_to_pay' => $netSalary + $mealVouchers + $ecoVouchers + $transportAllowance + $otherBenefits,
            'details' => [
                'employee_name' => $employee->full_name,
                'employee_niss' => $employee->niss,
                'calculation_date' => now()->toDateString(),
                'period' => "{$year}-{$month}",
            ]
        ];
    }

    /**
     * Calculer le précompte professionnel selon barème belge
     *
     * Barème simplifié 2024 (célibataire sans enfants)
     * - 0 - 15.200€: 25%
     * - 15.200 - 26.830€: 40%
     * - 26.830 - 46.440€: 45%
     * - > 46.440€: 50%
     */
    public static function calculateWithholdingTax(
        float $monthlyTaxableIncome,
        string $maritalStatus = 'single',
        int $dependents = 0,
        bool $hasDisability = false
    ): float {
        // Revenu annuel imposable
        $annualIncome = $monthlyTaxableIncome * 12;

        // Réductions pour personnes à charge
        $reduction = $dependents * 1750; // 1.750€ par personne à charge
        if ($hasDisability) {
            $reduction += 500; // 500€ si handicap
        }

        $taxableIncome = max(0, $annualIncome - $reduction);

        // Calcul impôt selon barème progressif
        $tax = 0;

        if ($taxableIncome > 46440) {
            $tax += ($taxableIncome - 46440) * 0.50;
            $taxableIncome = 46440;
        }
        if ($taxableIncome > 26830) {
            $tax += ($taxableIncome - 26830) * 0.45;
            $taxableIncome = 26830;
        }
        if ($taxableIncome > 15200) {
            $tax += ($taxableIncome - 15200) * 0.40;
            $taxableIncome = 15200;
        }
        if ($taxableIncome > 0) {
            $tax += $taxableIncome * 0.25;
        }

        // Réduction pour situation familiale
        if ($maritalStatus === 'married') {
            $tax *= 0.85; // -15% si marié
        }

        // Précompte mensuel
        return round($tax / 12, 2);
    }

    /**
     * Générer l'écriture comptable
     */
    public function generateJournalEntry(): array
    {
        return [
            'date' => now()->format('Y-m-d'),
            'reference' => "PAIE-{$this->year}-{$this->month}-{$this->employee->employee_number}",
            'description' => "Paie {$this->employee->full_name} - {$this->month}/{$this->year}",
            'lines' => [
                // Charge salariale brute
                [
                    'account' => '620', // Rémunérations
                    'debit' => $this->gross_salary,
                    'credit' => 0,
                    'description' => 'Salaire brut'
                ],
                // Charge patronale ONSS
                [
                    'account' => '621', // Cotisations patronales
                    'debit' => $this->onss_employer,
                    'credit' => 0,
                    'description' => 'ONSS patronale'
                ],
                // Dette ONSS totale
                [
                    'account' => '454', // ONSS
                    'debit' => 0,
                    'credit' => $this->onss_employee + $this->onss_employer,
                    'description' => 'ONSS employé + employeur'
                ],
                // Dette précompte professionnel
                [
                    'account' => '453', // Précompte professionnel
                    'debit' => 0,
                    'credit' => $this->withholding_tax,
                    'description' => 'Précompte professionnel'
                ],
                // Dette salaire net
                [
                    'account' => '455', // Rémunérations à payer
                    'debit' => 0,
                    'credit' => $this->net_salary,
                    'description' => 'Salaire net à payer'
                ],
            ]
        ];
    }

    /**
     * Calculer le montant total à verser à l'ONSS
     */
    public function getTotalOnss(): float
    {
        return $this->onss_employee + $this->onss_employer;
    }

    /**
     * Obtenir le coût total employeur
     */
    public function getTotalEmployerCost(): float
    {
        return $this->gross_salary + $this->onss_employer;
    }

    /**
     * Scope pour période
     */
    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope pour statut
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
