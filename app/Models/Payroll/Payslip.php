<?php

namespace App\Models\Payroll;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'payslip_number',
        'month',
        'year',
        'payment_date',
        'base_salary',
        'worked_days',
        'worked_hours',
        'transport_allowance',
        'food_allowance',
        'housing_allowance',
        'seniority_bonus',
        'performance_bonus',
        'overtime_pay',
        'other_bonuses',
        'gross_salary',
        'taxable_gross',
        'cnss_employee',
        'health_insurance_employee',
        'irpp_amount',
        'advance_payment',
        'loan_deduction',
        'other_deductions',
        'total_deductions',
        'cnss_employer',
        'accident_insurance',
        'health_insurance_employer',
        'net_salary',
        'net_to_pay',
        'status',
        'notes',
        'calculation_details',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'base_salary' => 'decimal:3',
        'worked_days' => 'decimal:2',
        'worked_hours' => 'decimal:2',
        'transport_allowance' => 'decimal:3',
        'food_allowance' => 'decimal:3',
        'housing_allowance' => 'decimal:3',
        'seniority_bonus' => 'decimal:3',
        'performance_bonus' => 'decimal:3',
        'overtime_pay' => 'decimal:3',
        'other_bonuses' => 'decimal:3',
        'gross_salary' => 'decimal:3',
        'taxable_gross' => 'decimal:3',
        'cnss_employee' => 'decimal:3',
        'health_insurance_employee' => 'decimal:3',
        'irpp_amount' => 'decimal:3',
        'advance_payment' => 'decimal:3',
        'loan_deduction' => 'decimal:3',
        'other_deductions' => 'decimal:3',
        'total_deductions' => 'decimal:3',
        'cnss_employer' => 'decimal:3',
        'accident_insurance' => 'decimal:3',
        'health_insurance_employer' => 'decimal:3',
        'net_salary' => 'decimal:3',
        'net_to_pay' => 'decimal:3',
        'calculation_details' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payslip) {
            if (!$payslip->payslip_number) {
                $payslip->payslip_number = self::generatePayslipNumber(
                    $payslip->company_id,
                    $payslip->year,
                    $payslip->month
                );
            }
        });
    }

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Méthodes
    private static function generatePayslipNumber(int $companyId, int $year, int $month): string
    {
        $lastPayslip = self::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastPayslip ? intval(substr($lastPayslip->payslip_number, -5)) + 1 : 1;

        return sprintf('PAY-%04d%02d-%05d', $year, $month, $number);
    }

    public function getTotalEmployerCostAttribute(): float
    {
        return $this->gross_salary + $this->cnss_employer + $this->accident_insurance;
    }

    public function getEmployerChargesAttribute(): float
    {
        return $this->cnss_employer + $this->accident_insurance + $this->health_insurance_employer;
    }

    // Scopes
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
