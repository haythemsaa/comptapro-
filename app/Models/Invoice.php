<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'customer_name',
        'customer_tax_id',
        'customer_address',
        'description',
        'total_ht',
        'vat_rate',
        'vat_amount',
        'total_ttc',
        'status',
        'is_accounted',
        'journal_entry_id',
        'imported_by_ai',
        'ai_confidence',
        'payment_status',
        'paid_amount',
        'paid_date',
        // El Fatoora
        'elfatoora_id',
        'elfatoora_signature',
        'elfatoora_qr_code',
        'elfatoora_status',
        'elfatoora_transmission_date',
        'elfatoora_validation_date',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'total_ht' => 'decimal:3',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:3',
        'total_ttc' => 'decimal:3',
        'paid_amount' => 'decimal:3',
        'is_accounted' => 'boolean',
        'imported_by_ai' => 'boolean',
        'ai_confidence' => 'decimal:2',
        'elfatoora_transmission_date' => 'datetime',
        'elfatoora_validation_date' => 'datetime',
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
     * Scope pour les factures non comptabilisées
     */
    public function scopePending($query)
    {
        return $query->where('is_accounted', false);
    }

    /**
     * Scope pour les factures importées par IA
     */
    public function scopeImportedByAI($query)
    {
        return $query->where('imported_by_ai', true);
    }

    /**
     * Vérifie si la facture est payée
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Vérifie si la facture est en retard
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now() && !$this->isPaid();
    }
}
