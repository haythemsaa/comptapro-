<?php

namespace App\Models\Banking;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'account_name',
        'account_number',
        'iban',
        'bic_swift',
        'bank_name',
        'branch',
        'currency',
        'account_code',
        'current_balance',
        'is_active',
        'bank_details',
    ];

    protected $casts = [
        'current_balance' => 'decimal:3',
        'is_active' => 'boolean',
        'bank_details' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function statements(): HasMany
    {
        return $this->hasMany(BankStatement::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function updateBalance(): void
    {
        $this->current_balance = $this->transactions()
            ->where('is_reconciled', true)
            ->sum('amount');

        $this->save();
    }
}
