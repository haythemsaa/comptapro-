<?php

namespace App\Models\Banking;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankStatement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'bank_account_id',
        'statement_number',
        'statement_date',
        'period_start',
        'period_end',
        'opening_balance',
        'ending_balance',
        'total_debits',
        'total_credits',
        'transaction_count',
        'file_path',
        'status',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'opening_balance' => 'decimal:3',
        'ending_balance' => 'decimal:3',
        'total_debits' => 'decimal:3',
        'total_credits' => 'decimal:3',
        'transaction_count' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function scopeForPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('statement_date', [$startDate, $endDate]);
    }

    public function scopeReconciled($query)
    {
        return $query->where('status', 'reconciled');
    }

    public function scopeImported($query)
    {
        return $query->where('status', 'imported');
    }

    public function calculateTotals(): void
    {
        $debits = $this->transactions()->where('type', 'debit')->sum('amount');
        $credits = $this->transactions()->where('type', 'credit')->sum('amount');

        $this->total_debits = abs($debits);
        $this->total_credits = abs($credits);
        $this->transaction_count = $this->transactions()->count();
        $this->ending_balance = $this->opening_balance + $credits - abs($debits);

        $this->save();
    }
}
