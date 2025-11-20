<?php

namespace App\Models\Belgium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model pour le Plan Comptable Minimum Normalisé (PCMN) Belgique
 *
 * @property int $id
 * @property string $account_number
 * @property string $account_name
 * @property string|null $account_name_nl
 * @property string|null $account_name_en
 * @property string|null $description
 * @property string $type
 * @property string $class
 * @property string|null $parent_account
 * @property int $level
 * @property bool $is_active
 * @property bool $is_system
 * @property bool $allow_direct_posting
 * @property array|null $tax_info
 * @property array|null $reporting_info
 */
class BelgiumChartOfAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'belgium_chart_of_accounts';

    protected $fillable = [
        'account_number',
        'account_name',
        'account_name_nl',
        'account_name_en',
        'description',
        'type',
        'class',
        'parent_account',
        'level',
        'is_active',
        'is_system',
        'allow_direct_posting',
        'tax_info',
        'reporting_info',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'allow_direct_posting' => 'boolean',
        'tax_info' => 'array',
        'reporting_info' => 'array',
        'level' => 'integer',
    ];

    /**
     * Types de comptes
     */
    const TYPE_ASSET = 'asset';
    const TYPE_LIABILITY = 'liability';
    const TYPE_EQUITY = 'equity';
    const TYPE_REVENUE = 'revenue';
    const TYPE_EXPENSE = 'expense';
    const TYPE_SPECIAL = 'special';

    /**
     * Classes PCMN
     */
    const CLASS_EQUITY_LIABILITIES = '1'; // Capitaux propres et passif
    const CLASS_FIXED_ASSETS = '2'; // Actif immobilisé
    const CLASS_INVENTORY = '3'; // Stocks
    const CLASS_CURRENT = '4'; // Créances et dettes à court terme
    const CLASS_FINANCIAL = '5'; // Trésorerie
    const CLASS_EXPENSES = '6'; // Charges
    const CLASS_REVENUES = '7'; // Produits
    const CLASS_OFF_BALANCE = '0'; // Hors bilan

    /**
     * Relation parent
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_account', 'account_number');
    }

    /**
     * Relation enfants
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_account', 'account_number');
    }

    /**
     * Scope pour comptes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour comptes d'un certain type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour comptes d'une certaine classe
     */
    public function scopeOfClass($query, $class)
    {
        return $query->where('class', $class);
    }

    /**
     * Scope pour comptes permettant la saisie directe
     */
    public function scopePostable($query)
    {
        return $query->where('allow_direct_posting', true);
    }

    /**
     * Obtenir le nom du compte selon la langue
     */
    public function getName($locale = 'fr')
    {
        return match($locale) {
            'nl' => $this->account_name_nl ?? $this->account_name,
            'en' => $this->account_name_en ?? $this->account_name,
            default => $this->account_name,
        };
    }

    /**
     * Vérifier si c'est un compte de bilan
     */
    public function isBalanceSheet(): bool
    {
        return in_array($this->type, [
            self::TYPE_ASSET,
            self::TYPE_LIABILITY,
            self::TYPE_EQUITY
        ]);
    }

    /**
     * Vérifier si c'est un compte de résultat
     */
    public function isIncomeStatement(): bool
    {
        return in_array($this->type, [
            self::TYPE_REVENUE,
            self::TYPE_EXPENSE
        ]);
    }

    /**
     * Obtenir le taux de TVA du compte
     */
    public function getVATRate(): ?float
    {
        return $this->tax_info['vat_rate'] ?? null;
    }

    /**
     * Obtenir le type de TVA
     */
    public function getVATType(): ?string
    {
        return $this->tax_info['vat_type'] ?? null;
    }

    /**
     * Obtenir l'arbre hiérarchique
     */
    public static function getTree()
    {
        return self::with('children')
            ->whereNull('parent_account')
            ->orderBy('account_number')
            ->get();
    }

    /**
     * Recherche de comptes
     */
    public static function search($query)
    {
        return self::where('account_number', 'like', "%{$query}%")
            ->orWhere('account_name', 'like', "%{$query}%")
            ->orWhere('account_name_nl', 'like', "%{$query}%")
            ->orWhere('account_name_en', 'like', "%{$query}%")
            ->orderBy('account_number')
            ->get();
    }
}
