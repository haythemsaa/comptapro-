<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Employee;
use App\Models\Payroll\Payslip;
use App\Models\Payroll\PayrollSettings;
use App\Services\Payroll\TunisianPayrollCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PayrollController extends Controller
{
    /**
     * Affiche la liste des employés
     */
    public function employees(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        $employees = Employee::where('company_id', $companyId)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_number', 'like', "%{$search}%")
                      ->orWhere('cin', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Payroll/Employees', [
            'employees' => $employees,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Crée un nouvel employé
     */
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cin' => 'required|string|unique:employees,cin',
            'cnss_number' => 'required|string|unique:employees,cnss_number',
            'birth_date' => 'required|date',
            'marital_status' => 'required|in:célibataire,marié,divorcé,veuf',
            'children_count' => 'nullable|integer|min:0',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'hire_date' => 'required|date',
            'position' => 'required|string',
            'department' => 'nullable|string',
            'contract_type' => 'required|in:CDI,CDD,CIVP,KARAMA,Stage',
            'base_salary' => 'required|numeric|min:0',
        ]);

        $employee = Employee::create(array_merge(
            $validated,
            ['company_id' => $request->user()->current_company_id]
        ));

        return redirect()->route('payroll.employees')->with('success', 'Employé créé avec succès');
    }

    /**
     * Génère un bulletin de paie
     */
    public function generatePayslip(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'transport_allowance' => 'nullable|numeric|min:0',
            'food_allowance' => 'nullable|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
        ]);

        $companyId = $request->user()->current_company_id;
        $employee = Employee::findOrFail($validated['employee_id']);

        // Vérifier si un bulletin existe déjà
        $existing = Payslip::where('company_id', $companyId)
            ->where('employee_id', $employee->id)
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return back()->withErrors(['message' => 'Un bulletin existe déjà pour cette période']);
        }

        // Calculer la paie
        $settings = PayrollSettings::where('company_id', $companyId)->first();
        $calculator = new TunisianPayrollCalculator($settings);

        $calculation = $calculator->calculatePayslip(
            $employee,
            $validated['month'],
            $validated['year'],
            $validated
        );

        // Créer le bulletin
        $payslip = Payslip::create(array_merge(
            [
                'company_id' => $companyId,
                'employee_id' => $employee->id,
                'month' => $validated['month'],
                'year' => $validated['year'],
                'payment_date' => now()->setYear($validated['year'])
                    ->setMonth($validated['month'])
                    ->lastOfMonth(),
            ],
            $calculation
        ));

        return redirect()->route('payroll.payslips')->with('success', 'Bulletin de paie généré avec succès');
    }

    /**
     * Liste des bulletins de paie
     */
    public function payslips(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        $payslips = Payslip::where('company_id', $companyId)
            ->with('employee')
            ->when($request->employee_id, function ($query, $employeeId) {
                $query->where('employee_id', $employeeId);
            })
            ->when($request->year, function ($query, $year) {
                $query->where('year', $year);
            })
            ->when($request->month, function ($query, $month) {
                $query->where('month', $month);
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        return Inertia::render('Payroll/Payslips', [
            'payslips' => $payslips,
            'filters' => $request->only(['employee_id', 'year', 'month']),
        ]);
    }

    /**
     * Génère la déclaration CNSS mensuelle
     */
    public function generateCNSSDeclaration(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
        ]);

        $companyId = $request->user()->current_company_id;

        // Récupérer tous les bulletins du mois
        $payslips = Payslip::where('company_id', $companyId)
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->whereIn('status', ['validated', 'paid'])
            ->get();

        if ($payslips->isEmpty()) {
            return back()->withErrors(['message' => 'Aucun bulletin validé pour cette période']);
        }

        // Calculer les totaux
        $totalGrossSalaries = $payslips->sum('gross_salary');
        $totalCNSSEmployee = $payslips->sum('cnss_employee');
        $totalCNSSEmployer = $payslips->sum('cnss_employer');
        $totalAccidentInsurance = $payslips->sum('accident_insurance');

        $declaration = \App\Models\Payroll\CNSSDeclaration::create([
            'company_id' => $companyId,
            'month' => $validated['month'],
            'year' => $validated['year'],
            'employee_count' => $payslips->count(),
            'total_gross_salaries' => $totalGrossSalaries,
            'total_cnss_employee' => $totalCNSSEmployee,
            'total_cnss_employer' => $totalCNSSEmployer,
            'total_cnss' => $totalCNSSEmployee + $totalCNSSEmployer,
            'total_accident_insurance' => $totalAccidentInsurance,
            'total_to_pay' => $totalCNSSEmployee + $totalCNSSEmployer + $totalAccidentInsurance,
            'status' => 'draft',
        ]);

        return redirect()->route('payroll.cnss-declarations')->with('success', 'Déclaration CNSS générée avec succès');
    }

    /**
     * Simule un calcul de paie
     */
    public function simulatePayroll(Request $request)
    {
        $validated = $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'marital_status' => 'nullable|in:célibataire,marié,divorcé,veuf',
            'children_count' => 'nullable|integer|min:0',
            'seniority_years' => 'nullable|numeric|min:0',
        ]);

        $settings = PayrollSettings::where('company_id', $request->user()->current_company_id)->first();
        $calculator = new TunisianPayrollCalculator($settings);

        // Créer un employé temporaire pour la simulation
        $tempEmployee = new Employee([
            'base_salary' => $validated['base_salary'],
            'marital_status' => $validated['marital_status'] ?? 'célibataire',
            'children_count' => $validated['children_count'] ?? 0,
            'hire_date' => now()->subYears($validated['seniority_years'] ?? 0),
        ]);

        $simulation = $calculator->calculatePayslip($tempEmployee, now()->month, now()->year);

        return response()->json($simulation);
    }
}
