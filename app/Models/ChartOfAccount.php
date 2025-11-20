<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'parent_id',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation avec l'entreprise
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relation avec le compte parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Relation avec les comptes enfants
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Relation avec les lignes d'écriture
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'account_id');
    }

    /**
     * Scope pour les comptes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour un type de compte
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les comptes de classe
     */
    public function scopeOfClass($query, string $class)
    {
        return $query->where('code', 'LIKE', $class . '%');
    }

    /**
     * Obtient le solde du compte
     */
    public function getBalance(\DateTime $endDate = null): float
    {
        $query = $this->journalLines()
            ->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->where('is_validated', true);
                if ($endDate) {
                    $q->where('date', '<=', $endDate);
                }
            });

        $debit = $query->sum('debit');
        $credit = $query->sum('credit');

        // Pour les comptes d'actif et de charges, le solde = débit - crédit
        // Pour les comptes de passif et de produits, le solde = crédit - débit
        if (in_array($this->type, ['asset', 'expense'])) {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }

    /**
     * Types de comptes
     */
    public static function getAccountTypes(): array
    {
        return [
            'asset' => 'Actif',
            'liability' => 'Passif',
            'equity' => 'Capitaux propres',
            'revenue' => 'Produits',
            'expense' => 'Charges',
        ];
    }
}
