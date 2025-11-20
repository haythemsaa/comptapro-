<?php

namespace App\Models\Belgium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Company;

/**
 * Model pour l'impôt des sociétés Belgique
 *
 * Taux IS Belgique 2024:
 * - 25%: Taux normal
 * - Taux réduit PME:
 *   - 20% sur les premiers 100.000€ de bénéfice
 *   - Conditions: capital minimum, rémunération minimum
 *
 * @property int $id
 * @property int $company_id
 * @property int $fiscal_year
 * @property float $accounting_profit
 * @property float $adjustments
 * @property float $taxable_profit
 * @property float $tax_rate
 * @property float $tax_amount
 * @property bool $is_sme
 * @property array $details
 */
class CompanyTaxBE extends Model
{
    use HasFactory;

    protected $table = 'company_taxes_be';

    protected $fillable = [
        'company_id',
        'fiscal_year',
        'accounting_profit',
        'deductible_expenses',
        'non_deductible_expenses',
        'tax_exempt_income',
        'adjustments',
        'taxable_profit',
        'tax_rate',
        'reduced_rate_applicable',
        'tax_normal_rate',
        'tax_reduced_rate',
        'tax_amount',
        'prepayments',
        'tax_to_pay',
        'tax_credits',
        'is_sme',
        'notional_interest_deduction', // Déduction intérêts notionnels
        'investment_deduction',        // Déduction investissements
        'details',
        'status',
        'filed_at',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'accounting_profit' => 'decimal:2',
        'deductible_expenses' => 'decimal:2',
        'non_deductible_expenses' => 'decimal:2',
        'tax_exempt_income' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'taxable_profit' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'reduced_rate_applicable' => 'boolean',
        'tax_normal_rate' => 'decimal:2',
        'tax_reduced_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'prepayments' => 'decimal:2',
        'tax_to_pay' => 'decimal:2',
        'tax_credits' => 'decimal:2',
        'is_sme' => 'boolean',
        'notional_interest_deduction' => 'decimal:2',
        'investment_deduction' => 'decimal:2',
        'details' => 'array',
        'filed_at' => 'datetime',
    ];

    /**
     * Taux IS Belgique 2024
     */
    const TAX_RATE_NORMAL = 0.25;          // 25%
    const TAX_RATE_SME_REDUCED = 0.20;     // 20% sur premiers 100.000€
    const SME_REDUCED_THRESHOLD = 100000;   // 100.000€

    /**
     * Conditions PME pour taux réduit
     */
    const SME_MIN_CAPITAL = 61500;         // Capital minimum 61.500€
    const SME_MIN_REMUNERATION = 45000;    // Rémunération min dirigeant 45.000€

    /**
     * Statuts
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_VALIDATED = 'validated';
    const STATUS_FILED = 'filed';
    const STATUS_PAID = 'paid';

    /**
     * Relations
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Calculer l'impôt des sociétés
     */
    public static function calculate(
        Company $company,
        int $fiscalYear,
        float $accountingProfit,
        array $additionalData = []
    ): array {
        // 1. Déterminer si PME éligible au taux réduit
        $isSME = self::isSMEEligible($company, $fiscalYear);

        // 2. Ajustements fiscaux
        $deductibleExpenses = $additionalData['deductible_expenses'] ?? 0;
        $nonDeductibleExpenses = $additionalData['non_deductible_expenses'] ?? 0;
        $taxExemptIncome = $additionalData['tax_exempt_income'] ?? 0;
        $notionalInterestDeduction = $additionalData['notional_interest_deduction'] ?? 0;
        $investmentDeduction = $additionalData['investment_deduction'] ?? 0;

        // 3. Résultat fiscal
        $taxableProfit = $accountingProfit
            - $deductibleExpenses
            + $nonDeductibleExpenses
            - $taxExemptIncome
            - $notionalInterestDeduction
            - $investmentDeduction;

        $taxableProfit = max(0, $taxableProfit);

        // 4. Calcul de l'impôt
        $taxNormalRate = 0;
        $taxReducedRate = 0;
        $reducedRateApplicable = false;

        if ($isSME && $taxableProfit > 0) {
            // PME: taux réduit sur premiers 100.000€
            $profitAtReducedRate = min($taxableProfit, self::SME_REDUCED_THRESHOLD);
            $profitAtNormalRate = max(0, $taxableProfit - self::SME_REDUCED_THRESHOLD);

            $taxReducedRate = round($profitAtReducedRate * self::TAX_RATE_SME_REDUCED, 2);
            $taxNormalRate = round($profitAtNormalRate * self::TAX_RATE_NORMAL, 2);
            $reducedRateApplicable = true;
        } else {
            // Taux normal 25%
            $taxNormalRate = round($taxableProfit * self::TAX_RATE_NORMAL, 2);
        }

        $taxAmount = $taxNormalRate + $taxReducedRate;

        // 5. Versements anticipés
        $prepayments = $additionalData['prepayments'] ?? 0;

        // 6. Impôt restant à payer
        $taxToPay = max(0, $taxAmount - $prepayments);

        return [
            'company_id' => $company->id,
            'fiscal_year' => $fiscalYear,
            'accounting_profit' => $accountingProfit,
            'deductible_expenses' => $deductibleExpenses,
            'non_deductible_expenses' => $nonDeductibleExpenses,
            'tax_exempt_income' => $taxExemptIncome,
            'adjustments' => $nonDeductibleExpenses - $deductibleExpenses,
            'taxable_profit' => $taxableProfit,
            'tax_rate' => $isSME ? self::TAX_RATE_SME_REDUCED : self::TAX_RATE_NORMAL,
            'reduced_rate_applicable' => $reducedRateApplicable,
            'tax_normal_rate' => $taxNormalRate,
            'tax_reduced_rate' => $taxReducedRate,
            'tax_amount' => $taxAmount,
            'prepayments' => $prepayments,
            'tax_to_pay' => $taxToPay,
            'is_sme' => $isSME,
            'notional_interest_deduction' => $notionalInterestDeduction,
            'investment_deduction' => $investmentDeduction,
            'status' => self::STATUS_DRAFT,
            'details' => [
                'calculation_date' => now()->toDateString(),
                'effective_rate' => $taxableProfit > 0 ? round(($taxAmount / $taxableProfit) * 100, 2) : 0,
            ]
        ];
    }

    /**
     * Vérifier si l'entreprise est éligible au taux réduit PME
     */
    protected static function isSMEEligible(Company $company, int $fiscalYear): bool
    {
        // Critères PME:
        // 1. Capital libéré minimum 61.500€
        // 2. Rémunération minimum dirigeant 45.000€
        // 3. Pas de groupe
        // 4. Moins de 10 employés en moyenne

        // TODO: Implémenter vérification complète
        // Pour l'instant, on suppose PME si capital >= 61.500€
        $capital = $company->capital ?? 0;

        return $capital >= self::SME_MIN_CAPITAL;
    }

    /**
     * Calculer la déduction pour intérêts notionnels
     * Belgique: déduction sur fonds propres corrigés
     * Taux 2024: environ 3.2% (variable annuellement)
     */
    public static function calculateNotionalInterestDeduction(
        float $equity,
        float $rate = 0.032
    ): float {
        return round($equity * $rate, 2);
    }

    /**
     * Calculer la déduction pour investissements
     * Déduction unique: 8% - 20% selon type d'investissement
     */
    public static function calculateInvestmentDeduction(
        float $investmentAmount,
        string $investmentType = 'standard'
    ): float {
        $rate = match($investmentType) {
            'digital' => 0.20,      // 20% pour investissements digitaux
            'energy' => 0.135,      // 13.5% pour économies d'énergie
            'r&d' => 0.20,          // 20% pour R&D
            default => 0.08,        // 8% standard
        };

        return round($investmentAmount * $rate, 2);
    }

    /**
     * Générer l'écriture comptable
     */
    public function generateJournalEntry(): array
    {
        return [
            'date' => now()->format('Y-m-d'),
            'reference' => "ISOC-{$this->fiscal_year}",
            'description' => "Impôt des sociétés {$this->fiscal_year}",
            'lines' => [
                // Charge d'impôt
                [
                    'account' => '670', // Impôts belges sur le résultat
                    'debit' => $this->tax_amount,
                    'credit' => 0,
                    'description' => "IS exercice {$this->fiscal_year}"
                ],
                // Dette fiscale
                [
                    'account' => '450', // Dettes fiscales estimées
                    'debit' => 0,
                    'credit' => $this->tax_amount,
                    'description' => "Dette IS {$this->fiscal_year}"
                ],
            ]
        ];
    }

    /**
     * Obtenir la date limite de dépôt
     * Belgique: 7 mois après clôture exercice
     */
    public function getFilingDeadlineAttribute(): \Carbon\Carbon
    {
        // Si clôture au 31/12, deadline au 30/09 année suivante
        return \Carbon\Carbon::create($this->fiscal_year, 12, 31)
            ->addMonths(7)
            ->endOfMonth();
    }

    /**
     * Obtenir le taux effectif
     */
    public function getEffectiveTaxRateAttribute(): float
    {
        if ($this->taxable_profit == 0) {
            return 0;
        }

        return round(($this->tax_amount / $this->taxable_profit) * 100, 2);
    }

    /**
     * Vérifier si en retard
     */
    public function isOverdue(): bool
    {
        return now()->isAfter($this->getFilingDeadlineAttribute())
            && $this->status !== self::STATUS_FILED
            && $this->status !== self::STATUS_PAID;
    }

    /**
     * Scopes
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSME($query)
    {
        return $query->where('is_sme', true);
    }
}
