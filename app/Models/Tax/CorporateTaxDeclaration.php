<?php

namespace App\Models\Tax;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorporateTaxDeclaration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'declaration_number',
        'fiscal_year',
        'fiscal_year_start',
        'fiscal_year_end',
        'accounting_profit',
        'accounting_loss',
        'reintegrations_total',
        'reintegrations_details',
        'deductions_total',
        'deductions_details',
        'taxable_profit',
        'tax_loss',
        'tax_loss_carryforward',
        'tax_rate',
        'corporate_tax_due',
        'advance_payment_q1',
        'advance_payment_q2',
        'advance_payment_q3',
        'total_advance_payments',
        'withholding_tax',
        'net_tax_to_pay',
        'tax_credit',
        'due_date',
        'submission_date',
        'payment_date',
        'status',
        'teledeclaration_reference',
        'declaration_data',
        'notes',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'fiscal_year_start' => 'date',
        'fiscal_year_end' => 'date',
        'accounting_profit' => 'decimal:3',
        'accounting_loss' => 'decimal:3',
        'reintegrations_total' => 'decimal:3',
        'reintegrations_details' => 'array',
        'deductions_total' => 'decimal:3',
        'deductions_details' => 'array',
        'taxable_profit' => 'decimal:3',
        'tax_loss' => 'decimal:3',
        'tax_loss_carryforward' => 'decimal:3',
        'tax_rate' => 'decimal:2',
        'corporate_tax_due' => 'decimal:3',
        'advance_payment_q1' => 'decimal:3',
        'advance_payment_q2' => 'decimal:3',
        'advance_payment_q3' => 'decimal:3',
        'total_advance_payments' => 'decimal:3',
        'withholding_tax' => 'decimal:3',
        'net_tax_to_pay' => 'decimal:3',
        'tax_credit' => 'decimal:3',
        'due_date' => 'date',
        'submission_date' => 'date',
        'payment_date' => 'date',
        'declaration_data' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(CorporateTaxAdvance::class);
    }

    public function scopeForFiscalYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function isLate(): bool
    {
        return $this->status !== 'paid' && now()->isAfter($this->due_date);
    }
}
