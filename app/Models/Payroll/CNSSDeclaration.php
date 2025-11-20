<?php

namespace App\Models\Payroll;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CNSSDeclaration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cnss_declarations';

    protected $fillable = [
        'company_id',
        'declaration_number',
        'month',
        'year',
        'employee_count',
        'total_gross_salaries',
        'total_cnss_employee',
        'total_cnss_employer',
        'total_cnss',
        'total_accident_insurance',
        'total_to_pay',
        'due_date',
        'payment_date',
        'status',
        'teledeclaration_reference',
        'declaration_data',
        'notes',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'employee_count' => 'integer',
        'total_gross_salaries' => 'decimal:3',
        'total_cnss_employee' => 'decimal:3',
        'total_cnss_employer' => 'decimal:3',
        'total_cnss' => 'decimal:3',
        'total_accident_insurance' => 'decimal:3',
        'total_to_pay' => 'decimal:3',
        'due_date' => 'date',
        'payment_date' => 'date',
        'declaration_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($declaration) {
            if (!$declaration->declaration_number) {
                $declaration->declaration_number = self::generateDeclarationNumber(
                    $declaration->company_id,
                    $declaration->year,
                    $declaration->month
                );
            }

            if (!$declaration->due_date) {
                $declaration->due_date = now()->setYear($declaration->year)
                    ->setMonth($declaration->month)
                    ->addMonth()
                    ->setDay(15);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    private static function generateDeclarationNumber(int $companyId, int $year, int $month): string
    {
        return sprintf('CNSS-%d-%04d%02d', $companyId, $year, $month);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'paid']);
    }

    public function isLate(): bool
    {
        return $this->status !== 'paid' && now()->isAfter($this->due_date);
    }
}
