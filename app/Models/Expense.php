<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'reference',
        'expense_date',
        'due_date',
        'supplier_name',
        'supplier_tax_id',
        'description',
        'category',
        'amount_ht',
        'vat_rate',
        'vat_amount',
        'amount_ttc',
        'status',
        'is_accounted',
        'journal_entry_id',
        'imported_by_ai',
        'ai_confidence',
        'payment_status',
        'paid_amount',
        'paid_date',
        'document_path',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount_ht' => 'decimal:3',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:3',
        'amount_ttc' => 'decimal:3',
        'paid_amount' => 'decimal:3',
        'is_accounted' => 'boolean',
        'imported_by_ai' => 'boolean',
        'ai_confidence' => 'decimal:2',
    ];

    /**
     * Relation avec l'entreprise
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relation avec l'écriture comptable
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Scope pour les dépenses non comptabilisées
     */
    public function scopePending($query)
    {
        return $query->where('is_accounted', false);
    }

    /**
     * Scope pour les dépenses importées par IA
     */
    public function scopeImportedByAI($query)
    {
        return $query->where('imported_by_ai', true);
    }

    /**
     * Catégories de dépenses tunisiennes
     */
    public static function getCategories(): array
    {
        return [
            'raw_materials' => 'Matières premières',
            'supplies' => 'Fournitures',
            'services' => 'Services',
            'maintenance' => 'Entretien et réparations',
            'insurance' => 'Assurances',
            'utilities' => 'Services publics',
            'marketing' => 'Publicité et marketing',
            'transport' => 'Transport',
            'travel' => 'Déplacements',
            'professional_fees' => 'Honoraires',
            'bank_charges' => 'Frais bancaires',
            'taxes' => 'Impôts et taxes',
            'salaries' => 'Salaires',
            'depreciation' => 'Amortissements',
            'other' => 'Autres',
        ];
    }
}
