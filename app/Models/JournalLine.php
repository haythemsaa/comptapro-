<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'label',
        'debit',
        'credit',
        'reconciled',
        'reconciliation_id',
    ];

    protected $casts = [
        'debit' => 'decimal:3',
        'credit' => 'decimal:3',
        'reconciled' => 'boolean',
    ];

    /**
     * Relation avec l'écriture
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Relation avec le compte
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Obtient le solde de la ligne (débit - crédit)
     */
    public function getBalance(): float
    {
        return $this->debit - $this->credit;
    }

    /**
     * Vérifie si c'est une ligne de débit
     */
    public function isDebit(): bool
    {
        return $this->debit > 0;
    }

    /**
     * Vérifie si c'est une ligne de crédit
     */
    public function isCredit(): bool
    {
        return $this->credit > 0;
    }
}
