<?php

namespace App\Http\Controllers\Admin\Tunisia;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Tunisia\PayrollTN;
use App\Models\Tunisia\EmployeeTN;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de gestion de la paie Tunisie
 */
class PayrollTNController extends Controller
{
    /**
     * Liste tous les bulletins de paie
     */
    public function index(Request $request)
    {
        $query = PayrollTN::with(['employee.company']);

        // Filtres
        if ($request->filled('company')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('company_id', $request->company);
            });
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%");
            });
        }

        $payrolls = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        // Options pour filtres
        $companies = Company::where('country_code', 'TN')->orderBy('name')->get(['id', 'name']);
        $years = range(date('Y'), date('Y') - 5);
        $months = range(1, 12);
        $statuses = ['draft', 'validated', 'paid'];

        return view('admin.tunisia.payrolls.index', compact('payrolls', 'companies', 'years', 'months', 'statuses'));
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

        return view('admin.tunisia.payrolls.create', compact('companies'));
    }

    /**
     * Générer les bulletins de paie pour une période
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'year' => 'required|integer|min:2020',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $company = Company::findOrFail($validated['company_id']);

        // Vérifier que c'est bien une entreprise tunisienne
        if ($company->country_code !== 'TN') {
            return back()->with('error', 'Cette entreprise n\'est pas tunisienne');
        }

        // Récupérer tous les employés actifs
        $employees = EmployeeTN::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'Aucun employé actif trouvé pour cette entreprise');
        }

        DB::beginTransaction();

        try {
            $generated = 0;
            $errors = [];

            foreach ($employees as $employee) {
                // Vérifier si un bulletin existe déjà
                $exists = PayrollTN::where('employee_id', $employee->id)
                    ->where('year', $validated['year'])
                    ->where('month', $validated['month'])
                    ->exists();

                if ($exists) {
                    $errors[] = "Bulletin déjà existant pour {$employee->full_name}";
                    continue;
                }

                // Calculer et créer le bulletin
                $calculation = PayrollTN::calculate($employee, $validated['year'], $validated['month']);

                PayrollTN::create(array_merge($calculation, [
                    'employee_id' => $employee->id,
                    'year' => $validated['year'],
                    'month' => $validated['month'],
                    'status' => 'draft',
                ]));

                $generated++;
            }

            DB::commit();

            $message = "{$generated} bulletin(s) de paie généré(s) avec succès";
            if (!empty($errors)) {
                $message .= '. Erreurs: ' . implode(', ', $errors);
            }

            return redirect()->route('admin.tunisia.payrolls.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un bulletin de paie
     */
    public function show(PayrollTN $payroll)
    {
        $payroll->load(['employee.company']);

        return view('admin.tunisia.payrolls.show', compact('payroll'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(PayrollTN $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Impossible de modifier un bulletin de paie déjà payé');
        }

        return view('admin.tunisia.payrolls.edit', compact('payroll'));
    }

    /**
     * Mettre à jour un bulletin de paie
     */
    public function update(Request $request, PayrollTN $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Impossible de modifier un bulletin de paie déjà payé');
        }

        $validated = $request->validate([
            'gross_salary' => 'required|numeric|min:0',
            'cnss_employee' => 'required|numeric|min:0',
            'irpp' => 'required|numeric|min:0',
            'css' => 'required|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'family_allowances' => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
        ]);

        // Recalculer le net_salary
        $validated['net_salary'] = $validated['gross_salary']
            - $validated['cnss_employee']
            - $validated['irpp']
            - $validated['css']
            - ($validated['other_deductions'] ?? 0)
            + ($validated['transport_allowance'] ?? 0)
            + ($validated['family_allowances'] ?? 0)
            + ($validated['other_allowances'] ?? 0);

        $payroll->update($validated);

        return redirect()->route('admin.tunisia.payrolls.show', $payroll)
            ->with('success', 'Bulletin de paie mis à jour avec succès');
    }

    /**
     * Valider un bulletin de paie
     */
    public function validate(PayrollTN $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Ce bulletin n\'est pas en brouillon');
        }

        $payroll->update(['status' => 'validated']);

        return back()->with('success', 'Bulletin de paie validé avec succès');
    }

    /**
     * Marquer comme payé
     */
    public function markAsPaid(PayrollTN $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Ce bulletin est déjà marqué comme payé');
        }

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Bulletin de paie marqué comme payé');
    }

    /**
     * Supprimer un bulletin de paie
     */
    public function destroy(PayrollTN $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Impossible de supprimer un bulletin de paie déjà payé');
        }

        $payroll->delete();

        return redirect()->route('admin.tunisia.payrolls.index')
            ->with('success', 'Bulletin de paie supprimé avec succès');
    }

    /**
     * Télécharger le PDF du bulletin
     */
    public function downloadPDF(PayrollTN $payroll)
    {
        // TODO: Implémenter la génération PDF
        return back()->with('info', 'Génération PDF à implémenter');
    }

    /**
     * Récupérer les employés par entreprise (API)
     */
    public function getEmployeesByCompany(Company $company)
    {
        $employees = EmployeeTN::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'cnss_number', 'gross_monthly_salary']);

        return response()->json($employees);
    }
}
