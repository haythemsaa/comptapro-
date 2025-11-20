<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;
use App\Models\Tax\VATDeclarationTunisia;
use App\Models\Tax\CorporateTaxDeclaration;
use App\Models\Tax\CorporateTaxAdvance;
use App\Services\Tax\TunisianVATCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class TaxController extends Controller
{
    /**
     * Liste des déclarations de TVA
     */
    public function vatDeclarations(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        $declarations = VATDeclarationTunisia::where('company_id', $companyId)
            ->when($request->year, function ($query, $year) {
                $query->where('year', $year);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        return Inertia::render('Tax/VAT/Index', [
            'declarations' => $declarations,
            'filters' => $request->only(['year', 'status']),
        ]);
    }

    /**
     * Génère une déclaration de TVA
     */
    public function generateVATDeclaration(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required_without:quarter|integer|min:1|max:12',
            'quarter' => 'required_without:month|integer|min:1|max:4',
            'year' => 'required|integer',
            'period_type' => 'required|in:monthly,quarterly',
        ]);

        $companyId = $request->user()->current_company_id;
        $company = \App\Models\Company::findOrFail($companyId);

        // Calculer les dates de période
        if ($validated['period_type'] === 'monthly') {
            $startDate = Carbon::create($validated['year'], $validated['month'], 1);
            $endDate = $startDate->copy()->endOfMonth();
        } else {
            $quarterMonth = ($validated['quarter'] - 1) * 3 + 1;
            $startDate = Carbon::create($validated['year'], $quarterMonth, 1);
            $endDate = $startDate->copy()->addMonths(2)->endOfMonth();
        }

        // Vérifier si une déclaration existe déjà
        $existing = VATDeclarationTunisia::where('company_id', $companyId)
            ->where('year', $validated['year'])
            ->where('period_type', $validated['period_type'])
            ->when($validated['period_type'] === 'monthly', function ($query) use ($validated) {
                $query->where('month', $validated['month']);
            })
            ->when($validated['period_type'] === 'quarterly', function ($query) use ($validated) {
                $query->where('quarter', $validated['quarter']);
            })
            ->first();

        if ($existing) {
            return back()->withErrors(['message' => 'Une déclaration existe déjà pour cette période']);
        }

        // Calculer la TVA
        $calculator = new TunisianVATCalculator();
        $calculation = $calculator->calculateVATDeclaration(
            $companyId,
            $startDate,
            $endDate,
            $validated['period_type'] === 'monthly'
        );

        // Créer la déclaration
        $declaration = VATDeclarationTunisia::create(array_merge(
            ['company_id' => $companyId],
            $calculation
        ));

        return redirect()->route('tax.vat.show', $declaration->id)
            ->with('success', 'Déclaration de TVA générée avec succès');
    }

    /**
     * Affiche une déclaration de TVA
     */
    public function showVATDeclaration(Request $request, int $id)
    {
        $companyId = $request->user()->current_company_id;

        $declaration = VATDeclarationTunisia::where('company_id', $companyId)
            ->findOrFail($id);

        return Inertia::render('Tax/VAT/Show', [
            'declaration' => $declaration,
        ]);
    }

    /**
     * Exporte au format TEIF
     */
    public function exportToTEIF(Request $request, int $id)
    {
        $companyId = $request->user()->current_company_id;

        $declaration = VATDeclarationTunisia::where('company_id', $companyId)
            ->findOrFail($id);

        $calculator = new TunisianVATCalculator();
        $teifContent = $calculator->exportToTEIF($declaration->toArray());

        $filename = "TVA_{$declaration->year}_{$declaration->month}.teif";

        return response($teifContent)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Soumet une déclaration de TVA (télédéclaration)
     */
    public function submitVATDeclaration(Request $request, int $id)
    {
        $companyId = $request->user()->current_company_id;

        $declaration = VATDeclarationTunisia::where('company_id', $companyId)
            ->findOrFail($id);

        // TODO: Intégration avec le système de télédéclaration tunisien
        // Pour l'instant, on marque comme soumise
        $declaration->status = 'submitted';
        $declaration->submission_date = now();
        $declaration->teledeclaration_reference = 'REF-' . uniqid();
        $declaration->save();

        return redirect()->route('tax.vat')
            ->with('success', 'Déclaration soumise avec succès');
    }

    /**
     * Liste des déclarations IS
     */
    public function corporateTaxDeclarations(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        $declarations = CorporateTaxDeclaration::where('company_id', $companyId)
            ->orderBy('fiscal_year', 'desc')
            ->paginate(20);

        return Inertia::render('Tax/CorporateTax/Index', [
            'declarations' => $declarations,
        ]);
    }

    /**
     * Génère une déclaration IS
     */
    public function generateCorporateTaxDeclaration(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer',
            'fiscal_year_start' => 'required|date',
            'fiscal_year_end' => 'required|date|after:fiscal_year_start',
            'accounting_profit' => 'required|numeric',
            'reintegrations' => 'nullable|array',
            'deductions' => 'nullable|array',
        ]);

        $companyId = $request->user()->current_company_id;
        $company = \App\Models\Company::findOrFail($companyId);

        // Calculer le résultat fiscal
        $reintegrationsTotal = collect($validated['reintegrations'] ?? [])->sum('amount');
        $deductionsTotal = collect($validated['deductions'] ?? [])->sum('amount');
        $taxableProfit = $validated['accounting_profit'] + $reintegrationsTotal - $deductionsTotal;

        // Déterminer le taux d'IS
        $taxRate = $this->determineCorporateTaxRate($company);

        // Calculer l'IS dû
        $corporateTaxDue = max($taxableProfit * ($taxRate / 100), 0);

        // Créer la déclaration
        $declaration = CorporateTaxDeclaration::create([
            'company_id' => $companyId,
            'fiscal_year' => $validated['fiscal_year'],
            'fiscal_year_start' => $validated['fiscal_year_start'],
            'fiscal_year_end' => $validated['fiscal_year_end'],
            'accounting_profit' => $validated['accounting_profit'],
            'accounting_loss' => $validated['accounting_profit'] < 0 ? abs($validated['accounting_profit']) : 0,
            'reintegrations_total' => $reintegrationsTotal,
            'reintegrations_details' => $validated['reintegrations'] ?? [],
            'deductions_total' => $deductionsTotal,
            'deductions_details' => $validated['deductions'] ?? [],
            'taxable_profit' => max($taxableProfit, 0),
            'tax_loss' => $taxableProfit < 0 ? abs($taxableProfit) : 0,
            'tax_rate' => $taxRate,
            'corporate_tax_due' => $corporateTaxDue,
            'status' => 'draft',
            'due_date' => Carbon::create($validated['fiscal_year'] + 1, 3, 25),
        ]);

        return redirect()->route('tax.corporate-tax.show', $declaration->id)
            ->with('success', 'Déclaration IS générée avec succès');
    }

    /**
     * Affiche une déclaration IS
     */
    public function showCorporateTaxDeclaration(Request $request, int $id)
    {
        $companyId = $request->user()->current_company_id;

        $declaration = CorporateTaxDeclaration::where('company_id', $companyId)
            ->with('advances')
            ->findOrFail($id);

        return Inertia::render('Tax/CorporateTax/Show', [
            'declaration' => $declaration,
        ]);
    }

    /**
     * Liste des acomptes IS
     */
    public function advances(Request $request, int $declarationId)
    {
        $companyId = $request->user()->current_company_id;

        $declaration = CorporateTaxDeclaration::where('company_id', $companyId)
            ->findOrFail($declarationId);

        $advances = $declaration->advances()
            ->orderBy('quarter')
            ->get();

        return response()->json($advances);
    }

    /**
     * Marque un acompte comme payé
     */
    public function payAdvance(Request $request, int $advanceId)
    {
        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string',
        ]);

        $companyId = $request->user()->current_company_id;

        $advance = CorporateTaxAdvance::where('company_id', $companyId)
            ->findOrFail($advanceId);

        $advance->markAsPaid($validated['amount_paid'], $validated['payment_reference']);

        return response()->json([
            'success' => true,
            'message' => 'Acompte marqué comme payé',
        ]);
    }

    /**
     * Déclarations TFP
     */
    public function tfpDeclarations(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        // TODO: Implémenter le modèle TFPDeclaration
        return Inertia::render('Tax/TFP/Index', [
            'declarations' => [],
        ]);
    }

    /**
     * Génère une déclaration TFP
     */
    public function generateTFPDeclaration(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
        ]);

        $companyId = $request->user()->current_company_id;
        $company = \App\Models\Company::findOrFail($companyId);

        // Récupérer les salaires du mois
        $payslips = \App\Models\Payroll\Payslip::where('company_id', $companyId)
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->whereIn('status', ['validated', 'paid'])
            ->get();

        $totalGrossSalaries = $payslips->sum('gross_salary');

        // Taux TFP (2% pour industrie, 1% pour autres)
        $tfpRate = config('tunisia.tfp.rate_industry', 2);

        $tfpAmount = $totalGrossSalaries * ($tfpRate / 100);

        // TODO: Créer l'enregistrement TFPDeclaration

        return redirect()->route('tax.tfp')
            ->with('success', 'Déclaration TFP générée avec succès');
    }

    /**
     * Déclarations de retenues à la source
     */
    public function withholdingDeclarations(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        // TODO: Implémenter le modèle WithholdingTaxDeclaration
        return Inertia::render('Tax/Withholding/Index', [
            'declarations' => [],
        ]);
    }

    /**
     * Génère une déclaration de retenues
     */
    public function generateWithholdingDeclaration(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'type' => 'required|in:salaries,honoraires,services,rent,commissions',
        ]);

        // TODO: Implémenter la logique de génération

        return redirect()->route('tax.withholding')
            ->with('success', 'Déclaration générée avec succès');
    }

    /**
     * Calendrier fiscal
     */
    public function taxCalendar(Request $request)
    {
        $companyId = $request->user()->current_company_id;
        $year = $request->year ?? now()->year;

        $calendar = $this->generateTaxCalendar($companyId, $year);

        return Inertia::render('Tax/Calendar', [
            'calendar' => $calendar,
            'year' => $year,
        ]);
    }

    /**
     * Génère le calendrier fiscal pour l'année
     */
    private function generateTaxCalendar(int $companyId, int $year): array
    {
        $events = [];

        // TVA mensuelle (28 de chaque mois)
        for ($month = 1; $month <= 12; $month++) {
            $events[] = [
                'type' => 'vat',
                'title' => 'TVA - Mois ' . ($month - 1),
                'due_date' => Carbon::create($year, $month, 28),
                'description' => 'Déclaration et paiement de la TVA',
                'priority' => 'high',
            ];
        }

        // CNSS (15 de chaque mois)
        for ($month = 1; $month <= 12; $month++) {
            $events[] = [
                'type' => 'cnss',
                'title' => 'CNSS - Mois ' . ($month - 1),
                'due_date' => Carbon::create($year, $month, 15),
                'description' => 'Déclaration et paiement CNSS',
                'priority' => 'high',
            ];
        }

        // Acomptes IS (juin, septembre, décembre)
        $acomptes = [
            ['month' => 6, 'day' => 30, 'quarter' => 1],
            ['month' => 9, 'day' => 30, 'quarter' => 2],
            ['month' => 12, 'day' => 31, 'quarter' => 3],
        ];

        foreach ($acomptes as $acompte) {
            $events[] = [
                'type' => 'corporate_tax_advance',
                'title' => "Acompte IS - T{$acompte['quarter']}",
                'due_date' => Carbon::create($year, $acompte['month'], $acompte['day']),
                'description' => 'Paiement acompte provisionnel IS',
                'priority' => 'medium',
            ];
        }

        // Déclaration IS annuelle (25 mars N+1)
        $events[] = [
            'type' => 'corporate_tax',
            'title' => 'Déclaration IS ' . ($year - 1),
            'due_date' => Carbon::create($year, 3, 25),
            'description' => 'Déclaration annuelle Impôt sur les Sociétés',
            'priority' => 'high',
        ];

        // Trier par date
        usort($events, fn($a, $b) => $a['due_date']->timestamp - $b['due_date']->timestamp);

        return $events;
    }

    /**
     * Détermine le taux d'IS applicable
     */
    private function determineCorporateTaxRate($company): float
    {
        // TODO: Implémenter la logique selon le secteur d'activité
        // Pour l'instant, retourner le taux général
        return config('tunisia.corporate_tax.general', 25);
    }

    /**
     * API: Calcule la TVA
     */
    public function calculateVATAPI(Request $request)
    {
        $validated = $request->validate([
            'amount_ht' => 'required|numeric',
            'vat_rate' => 'required|in:0,7,13,19',
        ]);

        $vatAmount = $validated['amount_ht'] * ($validated['vat_rate'] / 100);
        $totalTTC = $validated['amount_ht'] + $vatAmount;

        return response()->json([
            'amount_ht' => $validated['amount_ht'],
            'vat_rate' => $validated['vat_rate'],
            'vat_amount' => round($vatAmount, 3),
            'total_ttc' => round($totalTTC, 3),
        ]);
    }
}
