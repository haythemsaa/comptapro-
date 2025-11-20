<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AccountingAIAssistant;
use App\Services\AI\AnomalyDetectionService;
use App\Services\AI\CashFlowPredictor;
use App\Services\AI\IntelligentOCRService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AIAssistantController extends Controller
{
    /**
     * Page de l'assistant IA
     */
    public function index(Request $request)
    {
        $assistant = new AccountingAIAssistant();

        return Inertia::render('AI/Assistant', [
            'suggested_questions' => $assistant->getSuggestedQuestions(),
        ]);
    }

    /**
     * Pose une question à l'assistant IA
     */
    public function ask(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'context' => 'nullable|array',
        ]);

        $assistant = new AccountingAIAssistant();
        $response = $assistant->ask($validated['question'], $validated['context'] ?? []);

        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'error' => $response['error'] ?? 'Erreur lors de la requête',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'answer' => $response['answer'],
            'model' => $response['model'] ?? null,
        ]);
    }

    /**
     * Analyse financière avec IA
     */
    public function analyzeFinances(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        // Récupérer les métriques financières
        $metrics = $this->getFinancialMetrics($companyId);

        $assistant = new AccountingAIAssistant();
        $analysis = $assistant->analyzeFinances($metrics);

        return response()->json($analysis);
    }

    /**
     * Suggestions d'optimisation fiscale
     */
    public function suggestTaxOptimizations(Request $request)
    {
        $companyId = $request->user()->current_company_id;
        $company = \App\Models\Company::findOrFail($companyId);

        $companyData = [
            'annual_revenue' => $this->getAnnualRevenue($companyId),
            'sector' => $company->industry ?? 'Services',
            'legal_form' => $company->legal_structure ?? 'SARL',
            'employees_count' => $this->getEmployeesCount($companyId),
            'tax_regime' => 'Réel',
            'corporate_tax' => $this->getCorporateTax($companyId),
            'avg_monthly_vat' => $this->getAverageMonthlyVAT($companyId),
            'social_charges' => $this->getSocialCharges($companyId),
        ];

        $assistant = new AccountingAIAssistant();
        $suggestions = $assistant->suggestTaxOptimizations($companyData);

        return response()->json($suggestions);
    }

    /**
     * Catégorise une transaction avec IA
     */
    public function categorizeTransaction(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'type' => 'required|in:income,expense',
            'party_name' => 'nullable|string',
        ]);

        $assistant = new AccountingAIAssistant();
        $categorization = $assistant->categorizeTransaction($validated);

        return response()->json($categorization);
    }

    /**
     * Extraction OCR d'une facture
     */
    public function extractInvoiceData(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('temp/ocr');
        $fullPath = storage_path('app/' . $path);

        $ocr = new IntelligentOCRService();
        $result = $ocr->extractInvoiceData($fullPath);

        // Nettoyer le fichier temporaire
        unlink($fullPath);

        return response()->json($result);
    }

    /**
     * Prédiction de trésorerie
     */
    public function predictCashFlow(Request $request)
    {
        $validated = $request->validate([
            'days_ahead' => 'nullable|integer|min:7|max:365',
        ]);

        $companyId = $request->user()->current_company_id;
        $daysAhead = $validated['days_ahead'] ?? 90;

        $predictor = new CashFlowPredictor($companyId);
        $forecast = $predictor->predictCashFlow($daysAhead);

        return response()->json($forecast);
    }

    /**
     * Détection d'anomalies
     */
    public function detectAnomalies(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
        ]);

        $companyId = $request->user()->current_company_id;

        $options = [];
        if ($validated['period_start'] && $validated['period_end']) {
            $options['period'] = [
                'start' => $validated['period_start'],
                'end' => $validated['period_end'],
            ];
        }

        $detector = new AnomalyDetectionService($companyId);
        $report = $detector->detectAnomalies($options);

        return response()->json($report);
    }

    /**
     * Page du tableau de bord IA
     */
    public function dashboard(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        // Récupérer les insights IA en parallèle
        $predictor = new CashFlowPredictor($companyId);
        $detector = new AnomalyDetectionService($companyId);

        $cashFlowForecast = $predictor->predictCashFlow(30);
        $anomaliesReport = $detector->detectAnomalies();

        return Inertia::render('AI/Dashboard', [
            'cash_flow_forecast' => $cashFlowForecast,
            'anomalies' => $anomaliesReport,
            'financial_metrics' => $this->getFinancialMetrics($companyId),
        ]);
    }

    // Méthodes privées pour récupérer les données
    private function getFinancialMetrics(int $companyId): array
    {
        // Simplification - à adapter selon votre structure
        return [
            'revenue' => 250000,
            'expenses' => 180000,
            'operating_profit' => 70000,
            'net_profit' => 60000,
            'gross_margin' => 28,
            'net_margin' => 24,
            'cash_balance' => 45000,
            'receivables' => 35000,
            'payables' => 28000,
        ];
    }

    private function getAnnualRevenue(int $companyId): float
    {
        return \App\Models\Invoicing\Invoice::where('company_id', $companyId)
            ->whereYear('invoice_date', now()->year)
            ->whereIn('status', ['sent', 'paid'])
            ->sum('total_amount');
    }

    private function getEmployeesCount(int $companyId): int
    {
        return \App\Models\Payroll\Employee::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();
    }

    private function getCorporateTax(int $companyId): float
    {
        return 0; // À implémenter
    }

    private function getAverageMonthlyVAT(int $companyId): float
    {
        return 0; // À implémenter
    }

    private function getSocialCharges(int $companyId): float
    {
        return 0; // À implémenter
    }
}
