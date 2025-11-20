<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'year',
        'annual_entitlement',
        'days_taken',
        'days_remaining',
        'carried_forward',
    ];

    protected $casts = [
        'year' => 'integer',
        'annual_entitlement' => 'decimal:1',
        'days_taken' => 'decimal:1',
        'days_remaining' => 'decimal:1',
        'carried_forward' => 'decimal:1',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function updateBalance(): void
    {
        $approvedLeaves = LeaveRequest::where('employee_id', $this->employee_id)
            ->where('status', 'approved')
            ->whereYear('start_date', $this->year)
            ->where('leave_type', 'annual')
            ->sum('days_count');

        $this->days_taken = $approvedLeaves;
        $this->days_remaining = $this->annual_entitlement + $this->carried_forward - $this->days_taken;
        $this->save();
    }

    public static function createForEmployee(Employee $employee, int $year): self
    {
        return self::create([
            'employee_id' => $employee->id,
            'year' => $year,
            'annual_entitlement' => config('tunisia.leave.annual_days', 30),
            'days_taken' => 0,
            'days_remaining' => config('tunisia.leave.annual_days', 30),
            'carried_forward' => 0,
        ]);
    }
}
