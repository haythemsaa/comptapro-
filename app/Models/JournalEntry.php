<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'journal_type',
        'date',
        'reference',
        'description',
        'is_validated',
        'validated_at',
        'validated_by',
        'created_by',
        'created_by_ai',
        'ai_confidence',
        'ai_metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
        'created_by_ai' => 'boolean',
        'ai_confidence' => 'decimal:2',
        'ai_metadata' => 'array',
    ];

    /**
     * Relation avec l'entreprise
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relation avec les lignes d'écriture
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * Relation avec l'utilisateur créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur validateur
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scope pour les écritures non validées
     */
    public function scopePending($query)
    {
        return $query->where('is_validated', false);
    }

    /**
     * Scope pour les écritures créées par IA
     */
    public function scopeCreatedByAI($query)
    {
        return $query->where('created_by_ai', true);
    }

    /**
     * Scope pour les écritures haute confiance
     */
    public function scopeHighConfidence($query, float $threshold = 0.9)
    {
        return $query->where('created_by_ai', true)
            ->where('ai_confidence', '>=', $threshold);
    }

    /**
     * Vérifie si l'écriture est équilibrée
     */
    public function isBalanced(): bool
    {
        $totalDebit = $this->lines()->sum('debit');
        $totalCredit = $this->lines()->sum('credit');

        return abs($totalDebit - $totalCredit) < 0.01;
    }

    /**
     * Obtient le total débit
     */
    public function getTotalDebit(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    /**
     * Obtient le total crédit
     */
    public function getTotalCredit(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    /**
     * Types de journaux
     */
    public static function getJournalTypes(): array
    {
        return [
            'sales' => 'Ventes',
            'purchases' => 'Achats',
            'bank' => 'Banque',
            'cash' => 'Caisse',
            'various' => 'Opérations diverses',
            'payroll' => 'Paie',
            'inventory' => 'Inventaire',
        ];
    }
}
