<?php

namespace App\Models\Banking;

use App\Models\Accounting\JournalEntry;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'bank_account_id',
        'bank_statement_id',
        'transaction_date',
        'value_date',
        'reference',
        'description',
        'amount',
        'balance',
        'type',
        'category',
        'journal_entry_id',
        'is_reconciled',
        'reconciled_at',
        'reconciled_by',
        'reconciliation_type',
        'reconciliation_confidence',
        'metadata',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'value_date' => 'date',
        'amount' => 'decimal:3',
        'balance' => 'decimal:3',
        'is_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
        'reconciliation_confidence' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Déterminer automatiquement le type
            if (!$transaction->type) {
                $transaction->type = $transaction->amount >= 0 ? 'credit' : 'debit';
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    public function scopeForPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function reconcile(int $journalEntryId, int $userId, string $type = 'manual'): bool
    {
        $this->journal_entry_id = $journalEntryId;
        $this->is_reconciled = true;
        $this->reconciled_at = now();
        $this->reconciled_by = $userId;
        $this->reconciliation_type = $type;

        return $this->save();
    }

    public function unreconcile(): bool
    {
        $this->journal_entry_id = null;
        $this->is_reconciled = false;
        $this->reconciled_at = null;
        $this->reconciled_by = null;
        $this->reconciliation_type = null;
        $this->reconciliation_confidence = null;

        return $this->save();
    }
}
