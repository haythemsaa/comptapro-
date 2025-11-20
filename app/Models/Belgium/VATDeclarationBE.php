<?php

namespace App\Models\Belgium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Company;

/**
 * Model pour les déclarations TVA Belgique
 *
 * Périodicité Belgique:
 * - Mensuelle: CA > 2.500.000€/an
 * - Trimestrielle: CA < 2.500.000€/an
 *
 * Taux TVA Belgique 2024:
 * - 21%: Taux normal
 * - 12%: Taux intermédiaire (travaux immobiliers, restaurants)
 * - 6%: Taux réduit (aliments, livres, médicaments, transport)
 * - 0%: Export, intracommunautaire
 *
 * @property int $id
 * @property int $company_id
 * @property int $year
 * @property int $month
 * @property string $quarter
 * @property string $period_type
 * @property float $sales_21
 * @property float $vat_21
 * @property float $sales_12
 * @property float $vat_12
 * @property float $sales_6
 * @property float $vat_6
 * @property float $sales_0
 * @property float $sales_export
 * @property float $sales_intracommunity
 * @property float $purchases_domestic
 * @property float $vat_deductible
 * @property float $purchases_intracommunity
 * @property float $vat_intracommunity
 * @property float $vat_to_pay
 * @property float $vat_to_recover
 */
class VATDeclarationBE extends Model
{
    use HasFactory;

    protected $table = 'vat_declarations_be';

    protected $fillable = [
        'company_id',
        'year',
        'month',
        'quarter',
        'period_type',
        // Ventes et services - Grille 00-49
        'sales_21',         // Grille 01: Base CA 21%
        'vat_21',           // Grille 02: TVA 21%
        'sales_12',         // Grille 03: Base CA 12%
        'vat_12',           // Grille 04: TVA 12%
        'sales_6',          // Grille 05: Base CA 6%
        'vat_6',            // Grille 06: TVA 6%
        'sales_0',          // Grille 44: Opérations 0%
        'sales_export',     // Grille 46: Export hors UE
        'sales_intracommunity', // Grille 47: Livraisons intracommunautaires
        // Achats - Grille 81-88
        'purchases_domestic',       // Grille 81: Achats Belgique
        'vat_deductible',           // Grille 59: TVA déductible
        'purchases_intracommunity', // Grille 86: Achats intracommunautaires
        'vat_intracommunity',       // Grille 88: TVA intracommunautaire
        // Totaux
        'vat_collected',    // Total TVA collectée
        'vat_to_pay',       // TVA à payer (si positif)
        'vat_to_recover',   // TVA à récupérer (si négatif)
        'status',
        'submitted_at',
        'reference_number',
        'payment_reference', // Communication structurée
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'sales_21' => 'decimal:2',
        'vat_21' => 'decimal:2',
        'sales_12' => 'decimal:2',
        'vat_12' => 'decimal:2',
        'sales_6' => 'decimal:2',
        'vat_6' => 'decimal:2',
        'sales_0' => 'decimal:2',
        'sales_export' => 'decimal:2',
        'sales_intracommunity' => 'decimal:2',
        'purchases_domestic' => 'decimal:2',
        'vat_deductible' => 'decimal:2',
        'purchases_intracommunity' => 'decimal:2',
        'vat_intracommunity' => 'decimal:2',
        'vat_collected' => 'decimal:2',
        'vat_to_pay' => 'decimal:2',
        'vat_to_recover' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    /**
     * Taux TVA Belgique
     */
    const VAT_RATE_NORMAL = 0.21;      // 21%
    const VAT_RATE_INTERMEDIATE = 0.12; // 12%
    const VAT_RATE_REDUCED = 0.06;     // 6%
    const VAT_RATE_ZERO = 0.00;        // 0%

    /**
     * Types de période
     */
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';

    /**
     * Statuts
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_VALIDATED = 'validated';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PAID = 'paid';

    /**
     * Relations
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Calculer la déclaration TVA automatiquement
     */
    public static function calculate(
        Company $company,
        int $year,
        int $month,
        string $periodType = self::PERIOD_MONTHLY
    ): array {
        // Récupérer les factures de vente
        $salesData = self::getSalesData($company, $year, $month, $periodType);

        // Récupérer les achats
        $purchasesData = self::getPurchasesData($company, $year, $month, $periodType);

        // Calculer TVA collectée
        $vatCollected = $salesData['vat_21'] + $salesData['vat_12'] + $salesData['vat_6'];

        // Calculer TVA déductible
        $vatDeductible = $purchasesData['vat_deductible'] + $purchasesData['vat_intracommunity'];

        // TVA nette
        $vatNet = $vatCollected - $vatDeductible;

        // Déterminer le trimestre si nécessaire
        $quarter = null;
        if ($periodType === self::PERIOD_QUARTERLY) {
            $quarter = ceil($month / 3);
        }

        // Générer communication structurée pour paiement
        $paymentReference = self::generatePaymentReference($company, $year, $month, $quarter);

        return [
            'company_id' => $company->id,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'period_type' => $periodType,
            // Ventes
            'sales_21' => $salesData['sales_21'],
            'vat_21' => $salesData['vat_21'],
            'sales_12' => $salesData['sales_12'],
            'vat_12' => $salesData['vat_12'],
            'sales_6' => $salesData['sales_6'],
            'vat_6' => $salesData['vat_6'],
            'sales_0' => $salesData['sales_0'],
            'sales_export' => $salesData['sales_export'],
            'sales_intracommunity' => $salesData['sales_intracommunity'],
            // Achats
            'purchases_domestic' => $purchasesData['purchases_domestic'],
            'vat_deductible' => $purchasesData['vat_deductible'],
            'purchases_intracommunity' => $purchasesData['purchases_intracommunity'],
            'vat_intracommunity' => $purchasesData['vat_intracommunity'],
            // Totaux
            'vat_collected' => $vatCollected,
            'vat_to_pay' => $vatNet > 0 ? $vatNet : 0,
            'vat_to_recover' => $vatNet < 0 ? abs($vatNet) : 0,
            'payment_reference' => $paymentReference,
            'status' => self::STATUS_DRAFT,
        ];
    }

    /**
     * Récupérer les données de vente
     */
    protected static function getSalesData(Company $company, int $year, int $month, string $periodType): array
    {
        // TODO: Implémenter la récupération depuis les factures
        // Pour l'instant, retourne un tableau vide
        return [
            'sales_21' => 0,
            'vat_21' => 0,
            'sales_12' => 0,
            'vat_12' => 0,
            'sales_6' => 0,
            'vat_6' => 0,
            'sales_0' => 0,
            'sales_export' => 0,
            'sales_intracommunity' => 0,
        ];
    }

    /**
     * Récupérer les données d'achats
     */
    protected static function getPurchasesData(Company $company, int $year, int $month, string $periodType): array
    {
        // TODO: Implémenter la récupération depuis les factures
        return [
            'purchases_domestic' => 0,
            'vat_deductible' => 0,
            'purchases_intracommunity' => 0,
            'vat_intracommunity' => 0,
        ];
    }

    /**
     * Générer la communication structurée pour le paiement
     * Format: +++AAA/BBBB/CCCDD+++
     */
    public static function generatePaymentReference(Company $company, int $year, int $month, ?int $quarter = null): string
    {
        // Numéro d'entreprise (10 chiffres)
        $companyNumber = str_replace(['.', ' '], '', $company->vat_number ?? '0000000000');
        $companyNumber = substr($companyNumber, 0, 10);

        // Période
        if ($quarter) {
            $period = sprintf('%04d%d', $year, $quarter);
        } else {
            $period = sprintf('%04d%02d', $year, $month);
        }

        // Générer nombre de référence
        $reference = $companyNumber . $period;
        $reference = str_pad($reference, 10, '0', STR_PAD_LEFT);

        // Calcul modulo 97 pour la clé de contrôle
        $mod97 = $reference % 97;
        $checksum = $mod97 == 0 ? 97 : $mod97;

        // Formatter
        $part1 = substr($reference, 0, 3);
        $part2 = substr($reference, 3, 4);
        $part3 = substr($reference, 7, 3);

        return sprintf('+++%s/%s/%s%02d+++', $part1, $part2, $part3, $checksum);
    }

    /**
     * Obtenir le nom de la période
     */
    public function getPeriodNameAttribute(): string
    {
        if ($this->period_type === self::PERIOD_QUARTERLY) {
            return "Q{$this->quarter} {$this->year}";
        }
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }

    /**
     * Obtenir la date limite de dépôt
     * Belgique: 20 du mois suivant la période
     */
    public function getDeadlineAttribute(): \Carbon\Carbon
    {
        if ($this->period_type === self::PERIOD_QUARTERLY) {
            $lastMonthOfQuarter = $this->quarter * 3;
            return \Carbon\Carbon::create($this->year, $lastMonthOfQuarter, 1)
                ->addMonth()
                ->setDay(20);
        }

        return \Carbon\Carbon::create($this->year, $this->month, 1)
            ->addMonth()
            ->setDay(20);
    }

    /**
     * Vérifier si en retard
     */
    public function isOverdue(): bool
    {
        return now()->isAfter($this->getDeadlineAttribute())
            && $this->status !== self::STATUS_SUBMITTED
            && $this->status !== self::STATUS_PAID;
    }

    /**
     * Générer l'écriture comptable
     */
    public function generateJournalEntry(): array
    {
        $lines = [];

        if ($this->vat_to_pay > 0) {
            // TVA à payer
            $lines[] = [
                'account' => '451', // TVA à payer
                'debit' => $this->vat_to_pay,
                'credit' => 0,
                'description' => "TVA à payer {$this->period_name}"
            ];
            $lines[] = [
                'account' => '550', // Banque
                'debit' => 0,
                'credit' => $this->vat_to_pay,
                'description' => "Paiement TVA {$this->period_name}"
            ];
        } else if ($this->vat_to_recover > 0) {
            // TVA à récupérer
            $lines[] = [
                'account' => '411', // TVA à récupérer
                'debit' => $this->vat_to_recover,
                'credit' => 0,
                'description' => "TVA à récupérer {$this->period_name}"
            ];
        }

        return [
            'date' => now()->format('Y-m-d'),
            'reference' => "TVA-{$this->year}-{$this->month}",
            'description' => "Déclaration TVA {$this->period_name}",
            'lines' => $lines
        ];
    }

    /**
     * Scopes
     */
    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', [self::STATUS_SUBMITTED, self::STATUS_PAID])
            ->where(function($q) {
                $q->where(function($subq) {
                    // Mensuel
                    $subq->where('period_type', self::PERIOD_MONTHLY)
                        ->whereRaw("DATE(CONCAT(year, '-', month, '-01')) + INTERVAL 1 MONTH + INTERVAL 20 DAY < NOW()");
                })->orWhere(function($subq) {
                    // Trimestriel
                    $subq->where('period_type', self::PERIOD_QUARTERLY)
                        ->whereRaw("DATE(CONCAT(year, '-', quarter * 3, '-01')) + INTERVAL 1 MONTH + INTERVAL 20 DAY < NOW()");
                });
            });
    }
}
