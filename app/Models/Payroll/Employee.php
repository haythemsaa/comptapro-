<?php

namespace App\Models\Payroll;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_number',
        'first_name',
        'last_name',
        'cin',
        'cnss_number',
        'cnrps_number',
        'birth_date',
        'marital_status',
        'children_count',
        'email',
        'phone',
        'address',
        'hire_date',
        'end_date',
        'position',
        'department',
        'contract_type',
        'base_salary',
        'is_active',
        'bank_details',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'end_date' => 'date',
        'base_salary' => 'decimal:3',
        'is_active' => 'boolean',
        'children_count' => 'integer',
        'bank_details' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (!$employee->employee_number) {
                $employee->employee_number = self::generateEmployeeNumber($employee->company_id);
            }
        });
    }

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // Méthodes
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getSeniorityYearsAttribute(): float
    {
        return $this->hire_date->diffInYears(now());
    }

    public function getSeniorityBonusAttribute(): float
    {
        // Prime d'ancienneté selon la législation tunisienne
        // 5% après 2 ans, +2% tous les 2 ans jusqu'à 20 ans
        $years = $this->seniority_years;

        if ($years < 2) {
            return 0;
        }

        $bonus = 5; // 5% après 2 ans
        $years -= 2;

        // +2% tous les 2 ans
        $additionalPeriods = min(floor($years / 2), 15); // Max 30% (5% + 15*2%)
        $bonus += $additionalPeriods * 2;

        return min($bonus, 30); // Maximum 30%
    }

    public function calculateSeniorityAmount(): float
    {
        $bonusPercentage = $this->seniority_bonus;
        return round($this->base_salary * ($bonusPercentage / 100), 3);
    }

    public function getLeaveBalanceForYear(int $year): ?LeaveBalance
    {
        return $this->leaveBalances()->where('year', $year)->first();
    }

    public function getCurrentLeaveBalance(): ?LeaveBalance
    {
        return $this->getLeaveBalanceForYear(now()->year);
    }

    private static function generateEmployeeNumber(int $companyId): string
    {
        $lastEmployee = self::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastEmployee ? intval(substr($lastEmployee->employee_number, 4)) + 1 : 1;

        return 'EMP-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByContractType($query, string $contractType)
    {
        return $query->where('contract_type', $contractType);
    }
}
