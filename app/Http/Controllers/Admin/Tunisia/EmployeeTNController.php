<?php

namespace App\Http\Controllers\Admin\Tunisia;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Tunisia\EmployeeTN;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de gestion des employés Tunisie
 */
class EmployeeTNController extends Controller
{
    /**
     * Liste tous les employés
     */
    public function index(Request $request)
    {
        $query = EmployeeTN::with(['company']);

        // Filtres
        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('cin', 'like', "%{$request->search}%")
                  ->orWhere('cnss_number', 'like', "%{$request->search}%");
            });
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20);

        // Options pour filtres
        $companies = Company::where('country_code', 'TN')->orderBy('name')->get(['id', 'name']);

        return view('admin.tunisia.employees.index', compact('employees', 'companies'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $companies = Company::where('country_code', 'TN')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.tunisia.employees.create', compact('companies'));
    }

    /**
     * Enregistrer un nouvel employé
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cin' => 'required|string|unique:employees_tn,cin',
            'cnss_number' => 'required|string|unique:employees_tn,cnss_number',
            'date_of_birth' => 'required|date',
            'hire_date' => 'required|date',
            'position' => 'required|string',
            'gross_monthly_salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'bank_account_rib' => 'nullable|string',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'children_count' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $employee = EmployeeTN::create($validated);

        return redirect()->route('admin.tunisia.employees.show', $employee)
            ->with('success', 'Employé créé avec succès');
    }

    /**
     * Afficher les détails d'un employé
     */
    public function show(EmployeeTN $employee)
    {
        $employee->load(['company', 'payrolls' => function ($query) {
            $query->orderBy('year', 'desc')->orderBy('month', 'desc')->limit(12);
        }]);

        // Statistiques
        $stats = [
            'total_payrolls' => $employee->payrolls()->count(),
            'seniority_years' => $employee->hire_date->diffInYears(now()),
            'last_payroll' => $employee->payrolls()->latest('year')->latest('month')->first(),
        ];

        return view('admin.tunisia.employees.show', compact('employee', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(EmployeeTN $employee)
    {
        $companies = Company::where('country_code', 'TN')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.tunisia.employees.edit', compact('employee', 'companies'));
    }

    /**
     * Mettre à jour un employé
     */
    public function update(Request $request, EmployeeTN $employee)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cin' => 'required|string|unique:employees_tn,cin,' . $employee->id,
            'cnss_number' => 'required|string|unique:employees_tn,cnss_number,' . $employee->id,
            'date_of_birth' => 'required|date',
            'hire_date' => 'required|date',
            'position' => 'required|string',
            'gross_monthly_salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'bank_account_rib' => 'nullable|string',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'children_count' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $employee->update($validated);

        return redirect()->route('admin.tunisia.employees.show', $employee)
            ->with('success', 'Employé mis à jour avec succès');
    }

    /**
     * Activer/Désactiver un employé
     */
    public function toggleStatus(EmployeeTN $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);

        $status = $employee->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Employé {$status} avec succès");
    }

    /**
     * Supprimer un employé
     */
    public function destroy(EmployeeTN $employee)
    {
        if ($employee->payrolls()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un employé avec des bulletins de paie');
        }

        $employee->delete();

        return redirect()->route('admin.tunisia.employees.index')
            ->with('success', 'Employé supprimé avec succès');
    }

    /**
     * Calculer le bulletin de paie
     */
    public function calculatePayroll(Request $request, EmployeeTN $employee)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020',
            'month' => 'required|integer|min:1|max:12',
        ]);

        DB::beginTransaction();

        try {
            $calculation = \App\Models\Tunisia\PayrollTN::calculate(
                $employee,
                $validated['year'],
                $validated['month']
            );

            $payroll = \App\Models\Tunisia\PayrollTN::create(array_merge($calculation, [
                'employee_id' => $employee->id,
                'year' => $validated['year'],
                'month' => $validated['month'],
                'status' => 'draft',
            ]));

            DB::commit();

            return redirect()->route('admin.tunisia.payrolls.show', $payroll)
                ->with('success', 'Bulletin de paie calculé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du calcul: ' . $e->getMessage());
        }
    }
}
