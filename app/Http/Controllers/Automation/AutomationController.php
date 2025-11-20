<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Services\AI\SmartWorkflowService;
use App\Services\AI\AutoAccountingService;
use App\Services\AI\AutoFinancialReportsService;
use App\Services\AI\AutoTaxDeclarationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller pour l'automatisation complète de la comptabilité
 */
class AutomationController extends Controller
{
    /**
     * Affiche le dashboard d'automatisation
     */
    public function dashboard(Request $request): Response
    {
        $companyId = $request->user()->current_company_id;
        $workflow = new SmartWorkflowService($companyId);

        try {
            $dashboardData = $workflow->generateDashboard();

            return Inertia::render('Automation/Dashboard', [
                'dashboard' => $dashboardData
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return Inertia::render('Automation/Dashboard', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Active le pilote automatique
     */
    public function enableAutoPilot(Request $request): JsonResponse
    {
        $companyId = $request->user()->current_company_id;
        $workflow = new SmartWorkflowService($companyId);

        try {
            $result = $workflow->enableAutoPilot();

            return response()->json([
                'success' => true,
                'message' => 'Pilote automatique exécuté avec succès',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('AutoPilot error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exécute le workflow mensuel complet
     */
    public function executeMonthlyWorkflow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $companyId = $request->user()->current_company_id;
        $workflow = new SmartWorkflowService($companyId);

        try {
            $result = $workflow->executeCompleteMonthlyWorkflow(
                $validated['year'],
                $validated['month']
            );

            return response()->json([
                'success' => true,
                'message' => 'Workflow mensuel exécuté avec succès',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Monthly workflow error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exécute le workflow annuel complet
     */
    public function executeAnnualWorkflow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $companyId = $request->user()->current_company_id;
        $workflow = new SmartWorkflowService($companyId);

        try {
            $result = $workflow->executeCompleteAnnualWorkflow($validated['year']);

            return response()->json([
                'success' => true,
                'message' => 'Workflow annuel exécuté avec succès',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Annual workflow error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traite tous les documents en attente
     */
    public function processAllDocuments(Request $request): JsonResponse
    {
        $companyId = $request->user()->current_company_id;
        $autoAccounting = new AutoAccountingService($companyId);

        try {
            $result = $autoAccounting->processAllPendingDocuments();

            return response()->json([
                'success' => true,
                'message' => 'Documents traités avec succès',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Document processing error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload et traite un document automatiquement
     */
    public function uploadAndProcessDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'type' => 'required|in:invoice,expense',
        ]);

        $companyId = $request->user()->current_company_id;
        $workflow = new SmartWorkflowService($companyId);

        try {
            $file = $request->file('document');
            $path = $file->store('documents/pending', 'local');
            $fullPath = storage_path('app/' . $path);

            $result = $workflow->processDocumentEndToEnd($fullPath, $validated['type']);

            return response()->json([
                'success' => true,
                'message' => 'Document traité automatiquement par l\'IA',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Document upload error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génère automatiquement tous les états financiers
     */
    public function generateFinancialReports(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $companyId = $request->user()->current_company_id;
        $autoReports = new AutoFinancialReportsService($companyId);

        try {
            $result = $autoReports->generateAllFinancialReports(
                $validated['year'],
                $validated['month'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'États financiers générés automatiquement',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Financial reports error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génère automatiquement toutes les déclarations fiscales
     */
    public function generateTaxDeclarations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $companyId = $request->user()->current_company_id;
        $autoTax = new AutoTaxDeclarationService($companyId);

        try {
            $result = $autoTax->generateAllDeclarations(
                $validated['year'],
                $validated['month']
            );

            return response()->json([
                'success' => true,
                'message' => 'Déclarations fiscales générées automatiquement',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Tax declarations error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifie les deadlines à venir
     */
    public function checkDeadlines(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days_ahead' => 'nullable|integer|min:1|max:90',
        ]);

        $companyId = $request->user()->current_company_id;
        $autoTax = new AutoTaxDeclarationService($companyId);

        try {
            $result = $autoTax->checkUpcomingDeadlines($validated['days_ahead'] ?? 7);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Deadline check error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valide automatiquement les écritures haute confiance
     */
    public function autoValidateEntries(Request $request): JsonResponse
    {
        $companyId = $request->user()->current_company_id;
        $autoAccounting = new AutoAccountingService($companyId);

        try {
            $validatedCount = $autoAccounting->autoValidateHighConfidenceEntries();

            return response()->json([
                'success' => true,
                'message' => "{$validatedCount} écriture(s) validée(s) automatiquement",
                'validated_count' => $validatedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Auto validation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtient le statut de l'automatisation
     */
    public function getAutomationStatus(Request $request): JsonResponse
    {
        $companyId = $request->user()->current_company_id;

        try {
            // Documents en attente
            $pendingInvoices = \App\Models\Invoice::where('company_id', $companyId)
                ->where('is_accounted', false)
                ->count();

            $pendingExpenses = \App\Models\Expense::where('company_id', $companyId)
                ->where('is_accounted', false)
                ->count();

            // Écritures non validées créées par l'IA
            $aiEntries = \App\Models\JournalEntry::where('company_id', $companyId)
                ->where('created_by_ai', true)
                ->where('is_validated', false)
                ->count();

            // Deadlines à venir
            $autoTax = new AutoTaxDeclarationService($companyId);
            $upcomingDeadlines = $autoTax->checkUpcomingDeadlines(7);

            return response()->json([
                'success' => true,
                'status' => [
                    'pending_invoices' => $pendingInvoices,
                    'pending_expenses' => $pendingExpenses,
                    'ai_entries_to_validate' => $aiEntries,
                    'upcoming_deadlines' => $upcomingDeadlines['count'],
                    'urgent_deadlines' => collect($upcomingDeadlines['alerts'])
                        ->where('severity', 'high')
                        ->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Automation status error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
