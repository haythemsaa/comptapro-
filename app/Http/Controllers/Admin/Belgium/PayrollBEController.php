<?php

namespace App\Http\Controllers\Admin\Belgium;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Belgium\PayrollBE;
use App\Models\Belgium\EmployeeBE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de gestion de la paie Belgique
 */
class PayrollBEController extends Controller
{
    /**
     * Liste tous les bulletins de paie
     */
    public function index(Request $request)
    {
        $query = PayrollBE::with(['employee.company']);

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
        $companies = Company::where('country_code', 'BE')->orderBy('name')->get(['id', 'name']);
        $years = range(date('Y'), date('Y') - 5);
        $months = range(1, 12);
        $statuses = ['draft', 'validated', 'paid'];

        return view('admin.belgium.payrolls.index', compact('payrolls', 'companies', 'years', 'months', 'statuses'));
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

        return view('admin.belgium.payrolls.create', compact('companies'));
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

        // Vérifier que c'est bien une entreprise belge
        if ($company->country_code !== 'BE') {
            return back()->with('error', 'Cette entreprise n\'est pas belge');
        }

        // Récupérer tous les employés actifs
        $employees = EmployeeBE::where('company_id', $company->id)
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
                $exists = PayrollBE::where('employee_id', $employee->id)
                    ->where('year', $validated['year'])
                    ->where('month', $validated['month'])
                    ->exists();

                if ($exists) {
                    $errors[] = "Bulletin déjà existant pour {$employee->full_name}";
                    continue;
                }

                // Calculer et créer le bulletin
                $calculation = PayrollBE::calculate($employee, $validated['year'], $validated['month']);

                PayrollBE::create(array_merge($calculation, [
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

            return redirect()->route('admin.belgium.payrolls.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un bulletin de paie
     */
    public function show(PayrollBE $payroll)
    {
        $payroll->load(['employee.company']);

        // Calculer les totaux
        $totals = [
            'gross' => $payroll->gross_salary,
            'employee_deductions' => $payroll->onss_employee + $payroll->withholding_tax,
            'net' => $payroll->net_salary,
            'benefits' => $payroll->meal_vouchers + $payroll->eco_vouchers
                + $payroll->transport_allowance + $payroll->other_benefits,
            'net_to_pay' => $payroll->net_salary + $payroll->meal_vouchers
                + $payroll->eco_vouchers + $payroll->transport_allowance
                + $payroll->other_benefits,
            'employer_cost' => $payroll->employer_cost,
        ];

        return view('admin.belgium.payrolls.show', compact('payroll', 'totals'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(PayrollBE $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Impossible de modifier un bulletin de paie déjà payé');
        }

        return view('admin.belgium.payrolls.edit', compact('payroll'));
    }

    /**
     * Mettre à jour un bulletin de paie
     */
    public function update(Request $request, PayrollBE $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Impossible de modifier un bulletin de paie déjà payé');
        }

        $validated = $request->validate([
            'gross_salary' => 'required|numeric|min:0',
            'onss_employee' => 'required|numeric|min:0',
            'withholding_tax' => 'required|numeric|min:0',
            'onss_employer' => 'required|numeric|min:0',
            'meal_vouchers' => 'nullable|numeric|min:0',
            'eco_vouchers' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'holiday_pay' => 'nullable|numeric|min:0',
            'other_benefits' => 'nullable|numeric|min:0',
        ]);

        // Recalculer les montants
        $validated['net_salary'] = $validated['gross_salary']
            - $validated['onss_employee']
            - $validated['withholding_tax'];

        $validated['employer_cost'] = $validated['gross_salary']
            + $validated['onss_employer'];

        $payroll->update($validated);

        return redirect()->route('admin.belgium.payrolls.show', $payroll)
            ->with('success', 'Bulletin de paie mis à jour avec succès');
    }

    /**
     * Valider un bulletin de paie
     */
    public function validate(PayrollBE $payroll)
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
    public function markAsPaid(PayrollBE $payroll)
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
    public function destroy(PayrollBE $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Impossible de supprimer un bulletin de paie déjà payé');
        }

        $payroll->delete();

        return redirect()->route('admin.belgium.payrolls.index')
            ->with('success', 'Bulletin de paie supprimé avec succès');
    }

    /**
     * Télécharger le PDF du bulletin
     */
    public function downloadPDF(PayrollBE $payroll)
    {
        // Utiliser le service PDFGeneratorBE
        $pdfService = app(\App\Services\Belgium\PDFGeneratorBE::class);
        $pdf = $pdfService->generatePayslipPDF($payroll);

        return $pdf->download("fiche-paie-{$payroll->employee->full_name}-{$payroll->month}-{$payroll->year}.pdf");
    }

    /**
     * Récupérer les employés par entreprise (API)
     */
    public function getEmployeesByCompany(Company $company)
    {
        $employees = EmployeeBE::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'niss', 'gross_monthly_salary']);

        return response()->json($employees);
    }

    /**
     * Export DIMONA (Déclaration immédiate / Onmiddellijke Aangifte)
     */
    public function exportDimona(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        // TODO: Implémenter l'export DIMONA
        return back()->with('info', 'Export DIMONA à implémenter');
    }

    /**
     * Export DmfA (Déclaration multifonctionnelle)
     */
    public function exportDmfa(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'quarter' => 'required|integer|min:1|max:4',
            'year' => 'required|integer',
        ]);

        // TODO: Implémenter l'export DmfA
        return back()->with('info', 'Export DmfA à implémenter');
    }
}
