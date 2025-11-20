<?php

namespace App\Models\Payroll;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'cnss_employee_rate',
        'cnss_employer_rate',
        'accident_insurance_rate',
        'cnss_ceiling',
        'transport_tax_free',
        'irpp_brackets',
        'working_days_per_month',
        'working_hours_per_day',
        'apply_transport_bonus',
        'apply_seniority_bonus',
    ];

    protected $casts = [
        'cnss_employee_rate' => 'decimal:2',
        'cnss_employer_rate' => 'decimal:2',
        'accident_insurance_rate' => 'decimal:2',
        'cnss_ceiling' => 'decimal:3',
        'transport_tax_free' => 'decimal:3',
        'irpp_brackets' => 'array',
        'working_days_per_month' => 'integer',
        'working_hours_per_day' => 'decimal:2',
        'apply_transport_bonus' => 'boolean',
        'apply_seniority_bonus' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function getDefaultSettings(): array
    {
        return [
            'cnss_employee_rate' => 9.18,
            'cnss_employer_rate' => 16.57,
            'accident_insurance_rate' => 0.4,
            'cnss_ceiling' => 6000,
            'transport_tax_free' => 100,
            'irpp_brackets' => config('tunisia.irpp_brackets'),
            'working_days_per_month' => 26,
            'working_hours_per_day' => 8,
            'apply_transport_bonus' => true,
            'apply_seniority_bonus' => true,
        ];
    }
}
