<?php

namespace App\Http\Controllers\Admin\Belgium;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Belgium\VATDeclarationBE;
use App\Models\Belgium\CompanyTaxBE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de gestion des déclarations fiscales Belgique
 */
class TaxBEController extends Controller
{
    /**
     * Dashboard des déclarations fiscales
     */
    public function index(Request $request)
    {
        $companyId = $request->get('company');

        // Stats TVA
        $vatStats = [
            'pending' => VATDeclarationBE::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'draft')
                ->count(),
            'submitted' => VATDeclarationBE::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'submitted')
                ->count(),
            'total_amount' => VATDeclarationBE::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'submitted')
                ->sum('amount_due'),
        ];

        // Stats IS
        $isStats = [
            'pending' => CompanyTaxBE::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'draft')
                ->count(),
            'submitted' => CompanyTaxBE::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'submitted')
                ->count(),
            'total_amount' => CompanyTaxBE::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'submitted')
                ->sum('tax_amount'),
        ];

        // Dernières déclarations
        $recentVAT = VATDeclarationBE::with('company')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->orderBy('year', 'desc')
            ->orderBy('period', 'desc')
            ->limit(5)
            ->get();

        $recentIS = CompanyTaxBE::with('company')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->orderBy('year', 'desc')
            ->limit(5)
            ->get();

        $companies = Company::where('country_code', 'BE')->orderBy('name')->get(['id', 'name']);

        return view('admin.belgium.taxes.index', compact('vatStats', 'isStats', 'recentVAT', 'recentIS', 'companies'));
    }

    // ============== TVA ==============

    /**
     * Liste des déclarations TVA
     */
    public function vatIndex(Request $request)
    {
        $query = VATDeclarationBE::with('company');

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
            ->orderBy('period', 'desc')
            ->paginate(20);

        $companies = Company::where('country_code', 'BE')->orderBy('name')->get(['id', 'name']);
        $years = range(date('Y'), date('Y') - 5);
        $statuses = ['draft', 'submitted', 'paid'];

        return view('admin.belgium.taxes.vat.index', compact('declarations', 'companies', 'years', 'statuses'));
    }

    /**
     * Créer une déclaration TVA
     */
    public function vatCreate()
    {
        $companies = Company::where('country_code', 'BE')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.belgium.taxes.vat.create', compact('companies'));
    }

    /**
     * Enregistrer une déclaration TVA
     */
    public function vatStore(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'year' => 'required|integer|min:2020',
            'period' => 'required|in:M01,M02,M03,M04,M05,M06,M07,M08,M09,M10,M11,M12,Q1,Q2,Q3,Q4',
            'filing_type' => 'required|in:monthly,quarterly,annual',
            'turnover_21' => 'nullable|numeric|min:0',
            'turnover_12' => 'nullable|numeric|min:0',
            'turnover_6' => 'nullable|numeric|min:0',
            'turnover_intra_eu' => 'nullable|numeric|min:0',
            'turnover_export' => 'nullable|numeric|min:0',
            'vat_collected_21' => 'nullable|numeric|min:0',
            'vat_collected_12' => 'nullable|numeric|min:0',
            'vat_collected_6' => 'nullable|numeric|min:0',
            'vat_deductible' => 'nullable|numeric|min:0',
            'intra_eu_purchases' => 'nullable|numeric|min:0',
            'previous_credit' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Calculer le montant dû
            $totalCollected = ($validated['vat_collected_21'] ?? 0)
                + ($validated['vat_collected_12'] ?? 0)
                + ($validated['vat_collected_6'] ?? 0);

            $amountDue = $totalCollected
                - ($validated['vat_deductible'] ?? 0)
                - ($validated['previous_credit'] ?? 0);

            $declaration = VATDeclarationBE::create(array_merge($validated, [
                'total_vat_collected' => $totalCollected,
                'amount_due' => $amountDue,
                'status' => 'draft',
            ]));

            DB::commit();

            return redirect()->route('admin.belgium.taxes.vat.show', $declaration)
                ->with('success', 'Déclaration TVA créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Afficher une déclaration TVA
     */
    public function vatShow(VATDeclarationBE $declaration)
    {
        $declaration->load('company');

        // Grilles TVA belges
        $grids = [
            '00' => ['label' => 'Opérations soumises au régime général', 'amount' => $declaration->turnover_21],
            '01' => ['label' => 'TVA due (21%)', 'amount' => $declaration->vat_collected_21],
            '02' => ['label' => 'Opérations à 12%', 'amount' => $declaration->turnover_12],
            '03' => ['label' => 'TVA due (12%)', 'amount' => $declaration->vat_collected_12],
            '44' => ['label' => 'Opérations intra-UE', 'amount' => $declaration->turnover_intra_eu],
            '46' => ['label' => 'Livraisons intra-UE', 'amount' => $declaration->turnover_intra_eu],
            '47' => ['label' => 'Exportations hors UE', 'amount' => $declaration->turnover_export],
            '59' => ['label' => 'TVA déductible', 'amount' => $declaration->vat_deductible],
            '71' => ['label' => 'Montant dû', 'amount' => max(0, $declaration->amount_due)],
            '72' => ['label' => 'Crédit', 'amount' => max(0, -$declaration->amount_due)],
        ];

        return view('admin.belgium.taxes.vat.show', compact('declaration', 'grids'));
    }

    /**
     * Soumettre une déclaration TVA
     */
    public function vatSubmit(VATDeclarationBE $declaration)
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

    /**
     * Export Intervat (format XML)
     */
    public function vatExportIntervat(VATDeclarationBE $declaration)
    {
        // TODO: Implémenter l'export Intervat XML
        return back()->with('info', 'Export Intervat à implémenter');
    }

    // ============== Impôt des Sociétés ==============

    /**
     * Liste des déclarations IS
     */
    public function isIndex(Request $request)
    {
        $query = CompanyTaxBE::with('company');

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

        $companies = Company::where('country_code', 'BE')->orderBy('name')->get(['id', 'name']);
        $years = range(date('Y'), date('Y') - 5);
        $statuses = ['draft', 'submitted', 'paid'];

        return view('admin.belgium.taxes.is.index', compact('declarations', 'companies', 'years', 'statuses'));
    }

    /**
     * Créer une déclaration IS
     */
    public function isCreate()
    {
        $companies = Company::where('country_code', 'BE')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.belgium.taxes.is.create', compact('companies'));
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
            'notional_interest_deduction' => 'nullable|numeric|min:0',
            'investment_deduction' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Calcul du montant dû
            $amountDue = $validated['tax_amount']
                - ($validated['prepayments'] ?? 0)
                - ($validated['tax_credits'] ?? 0);

            $declaration = CompanyTaxBE::create(array_merge($validated, [
                'amount_due' => max(0, $amountDue),
                'status' => 'draft',
            ]));

            DB::commit();

            return redirect()->route('admin.belgium.taxes.is.show', $declaration)
                ->with('success', 'Déclaration IS créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Afficher une déclaration IS
     */
    public function isShow(CompanyTaxBE $declaration)
    {
        $declaration->load('company');

        // Calcul des taux effectifs
        $analytics = [
            'effective_rate' => $declaration->turnover > 0
                ? ($declaration->tax_amount / $declaration->turnover) * 100
                : 0,
            'profit_margin' => $declaration->turnover > 0
                ? ($declaration->taxable_profit / $declaration->turnover) * 100
                : 0,
            'sme_advantage' => $declaration->tax_rate == 0.20 ? true : false,
        ];

        return view('admin.belgium.taxes.is.show', compact('declaration', 'analytics'));
    }

    /**
     * Soumettre une déclaration IS
     */
    public function isSubmit(CompanyTaxBE $declaration)
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
     * Export Biztax (format XML)
     */
    public function isExportBiztax(CompanyTaxBE $declaration)
    {
        // TODO: Implémenter l'export Biztax XML
        return back()->with('info', 'Export Biztax à implémenter');
    }

    /**
     * Supprimer une déclaration
     */
    public function destroy(Request $request)
    {
        $type = $request->get('type'); // 'vat' or 'is'
        $id = $request->get('id');

        if ($type === 'vat') {
            $declaration = VATDeclarationBE::findOrFail($id);
            $route = 'admin.belgium.taxes.vat.index';
        } else {
            $declaration = CompanyTaxBE::findOrFail($id);
            $route = 'admin.belgium.taxes.is.index';
        }

        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Impossible de supprimer une déclaration soumise');
        }

        $declaration->delete();

        return redirect()->route($route)->with('success', 'Déclaration supprimée avec succès');
    }
}
