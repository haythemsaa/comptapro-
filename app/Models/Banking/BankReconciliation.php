<?php

namespace App\Models\Banking;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankReconciliation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'bank_account_id',
        'reconciliation_number',
        'reconciliation_date',
        'period_start',
        'period_end',
        'opening_balance_accounting',
        'opening_balance_bank',
        'ending_balance_accounting',
        'ending_balance_bank',
        'difference',
        'matched_count',
        'unmatched_bank_count',
        'unmatched_accounting_count',
        'matched_amount',
        'status',
        'approved_by',
        'approved_at',
        'reconciliation_data',
        'notes',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'opening_balance_accounting' => 'decimal:3',
        'opening_balance_bank' => 'decimal:3',
        'ending_balance_accounting' => 'decimal:3',
        'ending_balance_bank' => 'decimal:3',
        'difference' => 'decimal:3',
        'matched_count' => 'integer',
        'unmatched_bank_count' => 'integer',
        'unmatched_accounting_count' => 'integer',
        'matched_amount' => 'decimal:3',
        'approved_at' => 'datetime',
        'reconciliation_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reconciliation) {
            if (!$reconciliation->reconciliation_number) {
                $reconciliation->reconciliation_number = self::generateNumber(
                    $reconciliation->company_id
                );
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    private static function generateNumber(int $companyId): string
    {
        $last = self::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        $number = $last ? intval(substr($last->reconciliation_number, -5)) + 1 : 1;

        return 'REC-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function scopeForPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('reconciliation_date', [$startDate, $endDate]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function approve(int $userId): bool
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();

        return $this->save();
    }

    public function isBalanced(): bool
    {
        return abs($this->difference) < 0.01;
    }

    public function getReconciliationRate(): float
    {
        $total = $this->matched_count + $this->unmatched_bank_count + $this->unmatched_accounting_count;

        if ($total === 0) {
            return 0;
        }

        return ($this->matched_count / $total) * 100;
    }
}
