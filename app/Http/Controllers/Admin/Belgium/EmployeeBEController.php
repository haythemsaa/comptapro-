<?php

namespace App\Http\Controllers\Admin\Belgium;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Belgium\EmployeeBE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de gestion des employés Belgique
 */
class EmployeeBEController extends Controller
{
    /**
     * Liste tous les employés
     */
    public function index(Request $request)
    {
        $query = EmployeeBE::with(['company']);

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
                  ->orWhere('niss', 'like', "%{$request->search}%")
                  ->orWhere('employee_number', 'like', "%{$request->search}%");
            });
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20);

        // Options pour filtres
        $companies = Company::where('country_code', 'BE')->orderBy('name')->get(['id', 'name']);

        return view('admin.belgium.employees.index', compact('employees', 'companies'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $companies = Company::where('country_code', 'BE')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.belgium.employees.create', compact('companies'));
    }

    /**
     * Enregistrer un nouvel employé
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'employee_number' => 'required|string|unique:employees_be,employee_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'niss' => 'required|string|size:11|unique:employees_be,niss',
            'date_of_birth' => 'required|date',
            'hire_date' => 'required|date',
            'position' => 'required|string',
            'gross_monthly_salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'bank_account_iban' => 'nullable|string',
            'marital_status' => 'nullable|in:single,married,divorced,widowed,cohabiting',
            'children_count' => 'nullable|integer|min:0',
            'contract_type' => 'nullable|in:cdi,cdd,interim,student',
            'work_regime' => 'nullable|in:full_time,part_time,flexi',
            'is_active' => 'boolean',
        ]);

        // Validation NISS
        if (!$this->validateNISS($validated['niss'])) {
            return back()->withInput()->with('error', 'Numéro NISS invalide');
        }

        $employee = EmployeeBE::create($validated);

        return redirect()->route('admin.belgium.employees.show', $employee)
            ->with('success', 'Employé créé avec succès');
    }

    /**
     * Afficher les détails d'un employé
     */
    public function show(EmployeeBE $employee)
    {
        $employee->load(['company', 'payrolls' => function ($query) {
            $query->orderBy('year', 'desc')->orderBy('month', 'desc')->limit(12);
        }]);

        // Statistiques
        $stats = [
            'total_payrolls' => $employee->payrolls()->count(),
            'seniority_years' => $employee->hire_date->diffInYears(now()),
            'last_payroll' => $employee->payrolls()->latest('year')->latest('month')->first(),
            'average_net_salary' => $employee->payrolls()
                ->where('status', 'paid')
                ->avg('net_salary'),
        ];

        return view('admin.belgium.employees.show', compact('employee', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(EmployeeBE $employee)
    {
        $companies = Company::where('country_code', 'BE')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.belgium.employees.edit', compact('employee', 'companies'));
    }

    /**
     * Mettre à jour un employé
     */
    public function update(Request $request, EmployeeBE $employee)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'employee_number' => 'required|string|unique:employees_be,employee_number,' . $employee->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'niss' => 'required|string|size:11|unique:employees_be,niss,' . $employee->id,
            'date_of_birth' => 'required|date',
            'hire_date' => 'required|date',
            'position' => 'required|string',
            'gross_monthly_salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'bank_account_iban' => 'nullable|string',
            'marital_status' => 'nullable|in:single,married,divorced,widowed,cohabiting',
            'children_count' => 'nullable|integer|min:0',
            'contract_type' => 'nullable|in:cdi,cdd,interim,student',
            'work_regime' => 'nullable|in:full_time,part_time,flexi',
            'is_active' => 'boolean',
        ]);

        // Validation NISS si modifié
        if ($validated['niss'] !== $employee->niss && !$this->validateNISS($validated['niss'])) {
            return back()->withInput()->with('error', 'Numéro NISS invalide');
        }

        $employee->update($validated);

        return redirect()->route('admin.belgium.employees.show', $employee)
            ->with('success', 'Employé mis à jour avec succès');
    }

    /**
     * Activer/Désactiver un employé
     */
    public function toggleStatus(EmployeeBE $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);

        $status = $employee->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Employé {$status} avec succès");
    }

    /**
     * Supprimer un employé
     */
    public function destroy(EmployeeBE $employee)
    {
        if ($employee->payrolls()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un employé avec des bulletins de paie');
        }

        $employee->delete();

        return redirect()->route('admin.belgium.employees.index')
            ->with('success', 'Employé supprimé avec succès');
    }

    /**
     * Calculer le bulletin de paie
     */
    public function calculatePayroll(Request $request, EmployeeBE $employee)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020',
            'month' => 'required|integer|min:1|max:12',
        ]);

        DB::beginTransaction();

        try {
            $calculation = \App\Models\Belgium\PayrollBE::calculate(
                $employee,
                $validated['year'],
                $validated['month']
            );

            $payroll = \App\Models\Belgium\PayrollBE::create(array_merge($calculation, [
                'employee_id' => $employee->id,
                'year' => $validated['year'],
                'month' => $validated['month'],
                'status' => 'draft',
            ]));

            DB::commit();

            return redirect()->route('admin.belgium.payrolls.show', $payroll)
                ->with('success', 'Bulletin de paie calculé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du calcul: ' . $e->getMessage());
        }
    }

    /**
     * Valider le NISS belge
     */
    protected function validateNISS(string $niss): bool
    {
        // Le NISS belge doit avoir 11 chiffres
        if (!preg_match('/^\d{11}$/', $niss)) {
            return false;
        }

        // Validation checksum (algorithme modulo 97)
        $base = substr($niss, 0, 9);
        $checkDigits = (int) substr($niss, 9, 2);

        $remainder = (int) $base % 97;
        $expectedCheck = 97 - $remainder;

        // Pour les personnes nées après 2000, on ajoute 2 au début
        if ($expectedCheck !== $checkDigits) {
            $base2000 = '2' . $base;
            $remainder = (int) $base2000 % 97;
            $expectedCheck = 97 - $remainder;
        }

        return $expectedCheck === $checkDigits;
    }
}
