<?php

namespace App\Http\Resources\Belgium;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollBEResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => [
                'id' => $this->employee->id,
                'full_name' => $this->employee->full_name,
                'employee_number' => $this->employee->employee_number,
                'niss' => $this->employee->formatted_niss,
            ],
            'period' => [
                'year' => $this->year,
                'month' => $this->month,
                'display' => "{$this->month}/{$this->year}",
            ],
            'amounts' => [
                'gross_salary' => [
                    'value' => (float) $this->gross_salary,
                    'formatted' => number_format($this->gross_salary, 2, ',', '.') . '€',
                ],
                'onss_employee' => [
                    'value' => (float) $this->onss_employee,
                    'formatted' => number_format($this->onss_employee, 2, ',', '.') . '€',
                    'rate' => '13.07%',
                ],
                'taxable_salary' => [
                    'value' => (float) ($this->gross_salary - $this->onss_employee),
                    'formatted' => number_format($this->gross_salary - $this->onss_employee, 2, ',', '.') . '€',
                ],
                'withholding_tax' => [
                    'value' => (float) $this->withholding_tax,
                    'formatted' => number_format($this->withholding_tax, 2, ',', '.') . '€',
                ],
                'net_salary' => [
                    'value' => (float) $this->net_salary,
                    'formatted' => number_format($this->net_salary, 2, ',', '.') . '€',
                ],
                'onss_employer' => [
                    'value' => (float) $this->onss_employer,
                    'formatted' => number_format($this->onss_employer, 2, ',', '.') . '€',
                    'rate' => '~27%',
                ],
                'employer_cost' => [
                    'value' => (float) $this->employer_cost,
                    'formatted' => number_format($this->employer_cost, 2, ',', '.') . '€',
                ],
            ],
            'benefits' => [
                'holiday_pay' => (float) $this->holiday_pay,
                'meal_vouchers' => (float) $this->meal_vouchers,
                'eco_vouchers' => (float) $this->eco_vouchers,
                'transport_allowance' => (float) $this->transport_allowance,
                'other_benefits' => (float) $this->other_benefits,
                'total' => (float) ($this->meal_vouchers + $this->eco_vouchers + $this->transport_allowance + $this->other_benefits),
            ],
            'net_to_pay' => [
                'value' => (float) ($this->net_salary + $this->meal_vouchers + $this->eco_vouchers + $this->transport_allowance + $this->other_benefits),
                'formatted' => number_format($this->net_salary + $this->meal_vouchers + $this->eco_vouchers + $this->transport_allowance + $this->other_benefits, 2, ',', '.') . '€',
            ],
            'status' => $this->status,
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
