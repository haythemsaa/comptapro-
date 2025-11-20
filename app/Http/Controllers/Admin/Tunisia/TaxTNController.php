<?php

namespace App\Http\Controllers\Admin\Tunisia;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Tunisia\VATDeclarationTN;
use App\Models\Tunisia\CompanyTaxTN;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de gestion des déclarations fiscales Tunisie
 */
class TaxTNController extends Controller
{
    /**
     * Dashboard des déclarations fiscales
     */
    public function index(Request $request)
    {
        $companyId = $request->get('company');

        // Stats TVA
        $vatStats = [
            'pending' => VATDeclarationTN::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'draft')
                ->count(),
            'submitted' => VATDeclarationTN::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'submitted')
                ->count(),
            'total_amount' => VATDeclarationTN::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'submitted')
                ->sum('amount_due'),
        ];

        // Stats IS
        $isStats = [
            'pending' => CompanyTaxTN::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'draft')
                ->count(),
            'submitted' => CompanyTaxTN::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'submitted')
                ->count(),
            'total_amount' => CompanyTaxTN::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'submitted')
                ->sum('tax_amount'),
        ];

        // Dernières déclarations
        $recentVAT = VATDeclarationTN::with('company')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(5)
            ->get();

        $recentIS = CompanyTaxTN::with('company')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->orderBy('year', 'desc')
            ->limit(5)
            ->get();

        $companies = Company::where('country_code', 'TN')->orderBy('name')->get(['id', 'name']);

        return view('admin.tunisia.taxes.index', compact('vatStats', 'isStats', 'recentVAT', 'recentIS', 'companies'));
    }

    // ============== TVA ==============

    /**
     * Liste des déclarations TVA
     */
    public function vatIndex(Request $request)
    {
        $query = VATDeclarationTN::with('company');

        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $declarations = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        $companies = Company::where('country_code', 'TN')->orderBy('name')->get(['id', 'name']);
        $years = range(date('Y'), date('Y') - 5);
        $statuses = ['draft', 'submitted', 'paid'];

        return view('admin.tunisia.taxes.vat.index', compact('declarations', 'companies', 'years', 'statuses'));
    }

    /**
     * Créer une déclaration TVA
     */
    public function vatCreate()
    {
        $companies = Company::where('country_code', 'TN')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.tunisia.taxes.vat.create', compact('companies'));
    }

    /**
     * Enregistrer une déclaration TVA
     */
    public function vatStore(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'year' => 'required|integer|min:2020',
            'month' => 'required|integer|min:1|max:12',
            'turnover_19' => 'nullable|numeric|min:0',
            'turnover_13' => 'nullable|numeric|min:0',
            'turnover_7' => 'nullable|numeric|min:0',
            'turnover_export' => 'nullable|numeric|min:0',
            'vat_collected_19' => 'nullable|numeric|min:0',
            'vat_collected_13' => 'nullable|numeric|min:0',
            'vat_collected_7' => 'nullable|numeric|min:0',
            'vat_deductible' => 'nullable|numeric|min:0',
            'previous_credit' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Calculer le montant dû
            $totalCollected = ($validated['vat_collected_19'] ?? 0)
                + ($validated['vat_collected_13'] ?? 0)
                + ($validated['vat_collected_7'] ?? 0);

            $amountDue = $totalCollected
                - ($validated['vat_deductible'] ?? 0)
                - ($validated['previous_credit'] ?? 0);

            $declaration = VATDeclarationTN::create(array_merge($validated, [
                'total_vat_collected' => $totalCollected,
                'amount_due' => $amountDue,
                'status' => 'draft',
            ]));

            DB::commit();

            return redirect()->route('admin.tunisia.taxes.vat.show', $declaration)
                ->with('success', 'Déclaration TVA créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Afficher une déclaration TVA
     */
    public function vatShow(VATDeclarationTN $declaration)
    {
        $declaration->load('company');

        return view('admin.tunisia.taxes.vat.show', compact('declaration'));
    }

    /**
     * Soumettre une déclaration TVA
     */
    public function vatSubmit(VATDeclarationTN $declaration)
    {
        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Cette déclaration n\'est pas en brouillon');
        }

        $declaration->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        return back()->with('success', 'Déclaration TVA soumise avec succès');
    }

    // ============== Impôt sur les Sociétés ==============

    /**
     * Liste des déclarations IS
     */
    public function isIndex(Request $request)
    {
        $query = CompanyTaxTN::with('company');

        if ($request->filled('company')) {
            $query->where('company_id', $request->company);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $declarations = $query->orderBy('year', 'desc')->paginate(20);

        $companies = Company::where('country_code', 'TN')->orderBy('name')->get(['id', 'name']);
        $years = range(date('Y'), date('Y') - 5);
        $statuses = ['draft', 'submitted', 'paid'];

        return view('admin.tunisia.taxes.is.index', compact('declarations', 'companies', 'years', 'statuses'));
    }

    /**
     * Créer une déclaration IS
     */
    public function isCreate()
    {
        $companies = Company::where('country_code', 'TN')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.tunisia.taxes.is.create', compact('companies'));
    }

    /**
     * Enregistrer une déclaration IS
     */
    public function isStore(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'year' => 'required|integer|min:2020',
            'turnover' => 'required|numeric|min:0',
            'total_expenses' => 'required|numeric|min:0',
            'taxable_profit' => 'required|numeric',
            'tax_rate' => 'required|numeric|min:0|max:1',
            'tax_amount' => 'required|numeric|min:0',
            'prepayments' => 'nullable|numeric|min:0',
            'tax_credits' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $amountDue = $validated['tax_amount']
                - ($validated['prepayments'] ?? 0)
                - ($validated['tax_credits'] ?? 0);

            $declaration = CompanyTaxTN::create(array_merge($validated, [
                'amount_due' => max(0, $amountDue),
                'status' => 'draft',
            ]));

            DB::commit();

            return redirect()->route('admin.tunisia.taxes.is.show', $declaration)
                ->with('success', 'Déclaration IS créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Afficher une déclaration IS
     */
    public function isShow(CompanyTaxTN $declaration)
    {
        $declaration->load('company');

        return view('admin.tunisia.taxes.is.show', compact('declaration'));
    }

    /**
     * Soumettre une déclaration IS
     */
    public function isSubmit(CompanyTaxTN $declaration)
    {
        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Cette déclaration n\'est pas en brouillon');
        }

        $declaration->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        return back()->with('success', 'Déclaration IS soumise avec succès');
    }

    /**
     * Supprimer une déclaration
     */
    public function destroy(Request $request)
    {
        $type = $request->get('type'); // 'vat' or 'is'
        $id = $request->get('id');

        if ($type === 'vat') {
            $declaration = VATDeclarationTN::findOrFail($id);
            $route = 'admin.tunisia.taxes.vat.index';
        } else {
            $declaration = CompanyTaxTN::findOrFail($id);
            $route = 'admin.tunisia.taxes.is.index';
        }

        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Impossible de supprimer une déclaration soumise');
        }

        $declaration->delete();

        return redirect()->route($route)->with('success', 'Déclaration supprimée avec succès');
    }
}
