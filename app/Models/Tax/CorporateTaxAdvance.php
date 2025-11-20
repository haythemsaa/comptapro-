<?php

namespace App\Models\Tax;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateTaxAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'corporate_tax_declaration_id',
        'advance_number',
        'fiscal_year',
        'quarter',
        'due_date',
        'amount_due',
        'amount_paid',
        'payment_date',
        'status',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'quarter' => 'integer',
        'due_date' => 'date',
        'amount_due' => 'decimal:3',
        'amount_paid' => 'decimal:3',
        'payment_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(CorporateTaxDeclaration::class, 'corporate_tax_declaration_id');
    }

    public function scopeForQuarter($query, int $fiscalYear, int $quarter)
    {
        return $query->where('fiscal_year', $fiscalYear)->where('quarter', $quarter);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function isLate(): bool
    {
        return $this->status !== 'paid' && now()->isAfter($this->due_date);
    }

    public function markAsPaid(float $amount, ?string $reference = null): bool
    {
        $this->amount_paid = $amount;
        $this->payment_date = now();
        $this->payment_reference = $reference;
        $this->status = 'paid';

        return $this->save();
    }
}
