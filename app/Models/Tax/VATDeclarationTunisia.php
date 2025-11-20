<?php

namespace App\Models\Tax;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VATDeclarationTunisia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vat_declarations_tunisia';

    protected $fillable = [
        'company_id',
        'declaration_number',
        'period_type',
        'month',
        'quarter',
        'year',
        'period_start',
        'period_end',
        'sales_19_ht',
        'sales_19_vat',
        'sales_13_ht',
        'sales_13_vat',
        'sales_7_ht',
        'sales_7_vat',
        'sales_export_ht',
        'sales_exempt_ht',
        'total_sales_ht',
        'total_vat_collected',
        'purchases_vat_immobilisations',
        'purchases_vat_goods',
        'purchases_vat_services',
        'purchases_vat_import',
        'total_vat_deductible',
        'vat_adjustments',
        'vat_credit_previous',
        'vat_to_pay',
        'vat_credit',
        'due_date',
        'submission_date',
        'payment_date',
        'status',
        'teledeclaration_reference',
        'payment_reference',
        'declaration_data',
        'notes',
    ];

    protected $casts = [
        'month' => 'integer',
        'quarter' => 'integer',
        'year' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'sales_19_ht' => 'decimal:3',
        'sales_19_vat' => 'decimal:3',
        'sales_13_ht' => 'decimal:3',
        'sales_13_vat' => 'decimal:3',
        'sales_7_ht' => 'decimal:3',
        'sales_7_vat' => 'decimal:3',
        'sales_export_ht' => 'decimal:3',
        'sales_exempt_ht' => 'decimal:3',
        'total_sales_ht' => 'decimal:3',
        'total_vat_collected' => 'decimal:3',
        'purchases_vat_immobilisations' => 'decimal:3',
        'purchases_vat_goods' => 'decimal:3',
        'purchases_vat_services' => 'decimal:3',
        'purchases_vat_import' => 'decimal:3',
        'total_vat_deductible' => 'decimal:3',
        'vat_adjustments' => 'decimal:3',
        'vat_credit_previous' => 'decimal:3',
        'vat_to_pay' => 'decimal:3',
        'vat_credit' => 'decimal:3',
        'due_date' => 'date',
        'submission_date' => 'date',
        'payment_date' => 'date',
        'declaration_data' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForPeriod($query, int $year, int $month = null, int $quarter = null)
    {
        $query->where('year', $year);

        if ($month) {
            $query->where('month', $month);
        }

        if ($quarter) {
            $query->where('quarter', $quarter);
        }

        return $query;
    }

    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'monthly');
    }

    public function scopeQuarterly($query)
    {
        return $query->where('period_type', 'quarterly');
    }

    public function isLate(): bool
    {
        return $this->status !== 'paid' && now()->isAfter($this->due_date);
    }

    public function getLatePenalty(): float
    {
        if (!$this->isLate() || $this->vat_to_pay <= 0) {
            return 0;
        }

        $monthsLate = ceil(now()->diffInDays($this->due_date) / 30);
        $penaltyRate = config('tunisia.late_penalties.vat_monthly_rate', 1.75) / 100;

        return round($this->vat_to_pay * $penaltyRate * $monthsLate, 3);
    }
}
