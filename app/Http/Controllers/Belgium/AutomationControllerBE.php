<?php

namespace App\Http\Controllers\Belgium;

use App\Http\Controllers\Controller;
use App\Services\Belgium\AutoAccountingServiceBE;
use App\Services\Belgium\AutoTaxServiceBE;
use App\Models\Company;
use App\Models\Belgium\VATDeclarationBE;
use App\Models\Belgium\PayrollBE;
use App\Models\Belgium\CompanyTaxBE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur d'automatisation pour la Belgique
 * Gère toutes les fonctionnalités d'automatisation IA
 */
class AutomationControllerBE extends Controller
{
    protected AutoAccountingServiceBE $accountingService;
    protected AutoTaxServiceBE $taxService;

    public function __construct(
        AutoAccountingServiceBE $accountingService,
        AutoTaxServiceBE $taxService
    ) {
        $this->accountingService = $accountingService;
        $this->taxService = $taxService;
    }

    /**
     * Dashboard d'automatisation Belgique
     */
    public function dashboard()
    {
        $company = Auth::user()->company;

        // Statistiques
        $stats = [
            'documents_processed' => 0, // TODO: récupérer depuis DB
            'vat_declarations' => VATDeclarationBE::where('company_id', $company->id)->count(),
            'payrolls_processed' => PayrollBE::where('company_id', $company->id)->count(),
            'company_taxes' => CompanyTaxBE::where('company_id', $company->id)->count(),
        ];

        // Échéances à venir
        $deadlines = $this->taxService->checkDeadlines($company, now()->year, now()->month);

        // Activité récente
        $recentActivity = $this->getRecentActivity($company);

        return view('belgium.automation.dashboard', compact('stats', 'deadlines', 'recentActivity'));
    }

    /**
     * Upload et traitement de document
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'type' => 'required|in:invoice,expense,receipt',
        ]);

        $company = Auth::user()->company;

        // Sauvegarder le fichier
        $path = $request->file('document')->store('documents/' . $company->id, 'private');
        $fullPath = storage_path('app/private/' . $path);

        // Traiter avec IA
        $result = $this->accountingService->processDocument(
            $company,
            $fullPath,
            $request->type
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Document traité avec succès',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Erreur de traitement',
        ], 500);
    }

    /**
     * Générer déclaration TVA
     */
    public function generateVATDeclaration(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'period_type' => 'required|in:monthly,quarterly',
        ]);

        $company = Auth::user()->company;

        $result = $this->taxService->generateVATDeclaration(
            $company,
            $request->year,
            $request->month,
            $request->period_type
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Déclaration TVA générée avec succès',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 500);
    }

    /**
     * Page des déclarations TVA
     */
    public function vatDeclarations()
    {
        $company = Auth::user()->company;

        $declarations = VATDeclarationBE::where('company_id', $company->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        // Prochaine échéance
        $nextDeadline = VATDeclarationBE::where('company_id', $company->id)
            ->where('status', VATDeclarationBE::STATUS_DRAFT)
            ->orderBy('year')
            ->orderBy('month')
            ->first();

        return view('belgium.automation.vat-declarations', compact('declarations', 'nextDeadline'));
    }

    /**
     * Générer déclaration ONSS
     */
    public function generateONSSDeclaration(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'quarter' => 'required|integer|min:1|max:4',
        ]);

        $company = Auth::user()->company;

        $result = $this->taxService->generateONSSDeclaration(
            $company,
            $request->year,
            $request->quarter
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Déclaration ONSS générée avec succès',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 500);
    }

    /**
     * Page de la paie
     */
    public function payroll()
    {
        $company = Auth::user()->company;

        $payrolls = PayrollBE::where('company_id', $company->id)
            ->with('employee')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        $currentMonth = now();
        $monthlyStats = PayrollBE::where('company_id', $company->id)
            ->forPeriod($currentMonth->year, $currentMonth->month)
            ->get();

        $stats = [
            'total_gross' => $monthlyStats->sum('gross_salary'),
            'total_net' => $monthlyStats->sum('net_salary'),
            'total_onss' => $monthlyStats->sum(function($p) {
                return $p->getTotalOnss();
            }),
            'employee_count' => $monthlyStats->count(),
        ];

        return view('belgium.automation.payroll', compact('payrolls', 'stats'));
    }

    /**
     * Générer déclaration IS (impôt des sociétés)
     */
    public function generateCompanyTaxDeclaration(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|integer|min:2020|max:2100',
        ]);

        $company = Auth::user()->company;

        $result = $this->taxService->generateCompanyTaxDeclaration(
            $company,
            $request->fiscal_year
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Déclaration IS générée avec succès',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 500);
    }

    /**
     * Page impôt des sociétés
     */
    public function companyTax()
    {
        $company = Auth::user()->company;

        $declarations = CompanyTaxBE::where('company_id', $company->id)
            ->orderBy('fiscal_year', 'desc')
            ->paginate(10);

        return view('belgium.automation.company-tax', compact('declarations'));
    }

    /**
     * Obtenir l'activité récente
     */
    protected function getRecentActivity(Company $company, int $limit = 10): array
    {
        $activities = [];

        // TVA récente
        $recentVAT = VATDeclarationBE::where('company_id', $company->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($recentVAT as $vat) {
            $activities[] = [
                'type' => 'vat',
                'icon' => 'bi-receipt',
                'title' => "Déclaration TVA {$vat->period_name}",
                'description' => "TVA à payer: {$vat->vat_to_pay}€",
                'date' => $vat->created_at,
                'status' => $vat->status,
            ];
        }

        // Paie récente
        $recentPayroll = PayrollBE::where('company_id', $company->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($recentPayroll as $payroll) {
            $activities[] = [
                'type' => 'payroll',
                'icon' => 'bi-people',
                'title' => "Paie {$payroll->employee->full_name ?? 'N/A'}",
                'description' => "Net: {$payroll->net_salary}€",
                'date' => $payroll->created_at,
                'status' => $payroll->status,
            ];
        }

        // Trier par date
        usort($activities, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * API: Statistiques globales
     */
    public function stats()
    {
        $company = Auth::user()->company;

        $currentYear = now()->year;
        $currentMonth = now()->month;

        return response()->json([
            'vat' => [
                'this_month' => VATDeclarationBE::where('company_id', $company->id)
                    ->forPeriod($currentYear, $currentMonth)
                    ->first(),
                'total_year' => VATDeclarationBE::where('company_id', $company->id)
                    ->where('year', $currentYear)
                    ->sum('vat_to_pay'),
            ],
            'payroll' => [
                'this_month' => PayrollBE::where('company_id', $company->id)
                    ->forPeriod($currentYear, $currentMonth)
                    ->sum('employer_cost'),
                'employee_count' => PayrollBE::where('company_id', $company->id)
                    ->forPeriod($currentYear, $currentMonth)
                    ->distinct('employee_id')
                    ->count(),
            ],
            'company_tax' => [
                'current_year' => CompanyTaxBE::where('company_id', $company->id)
                    ->where('fiscal_year', $currentYear)
                    ->first(),
            ]
        ]);
    }
}
