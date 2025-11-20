<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Automation\AutomationController;

/*
|--------------------------------------------------------------------------
| Automation Routes - ComptaPro Tunisia
|--------------------------------------------------------------------------
|
| Routes pour l'automatisation complète de la comptabilité avec IA
| Toutes les routes nécessitent une authentification
|
*/

Route::middleware(['auth:sanctum', 'ensure.tunisian.company'])->group(function () {

    // Dashboard d'automatisation
    Route::get('/automation/dashboard', [AutomationController::class, 'dashboard'])
        ->name('automation.dashboard');

    // Pilote automatique
    Route::post('/automation/auto-pilot', [AutomationController::class, 'enableAutoPilot'])
        ->name('automation.auto_pilot');

    // Workflows
    Route::post('/automation/workflow/monthly', [AutomationController::class, 'executeMonthlyWorkflow'])
        ->name('automation.workflow.monthly');

    Route::post('/automation/workflow/annual', [AutomationController::class, 'executeAnnualWorkflow'])
        ->name('automation.workflow.annual');

    // Documents
    Route::post('/automation/documents/process-all', [AutomationController::class, 'processAllDocuments'])
        ->name('automation.documents.process_all');

    Route::post('/automation/documents/upload', [AutomationController::class, 'uploadAndProcessDocument'])
        ->name('automation.documents.upload');

    // États financiers automatiques
    Route::post('/automation/financial-reports/generate', [AutomationController::class, 'generateFinancialReports'])
        ->name('automation.financial_reports.generate');

    // Déclarations fiscales automatiques
    Route::post('/automation/tax-declarations/generate', [AutomationController::class, 'generateTaxDeclarations'])
        ->name('automation.tax_declarations.generate');

    // Deadlines
    Route::get('/automation/deadlines/check', [AutomationController::class, 'checkDeadlines'])
        ->name('automation.deadlines.check');

    // Validation automatique
    Route::post('/automation/entries/auto-validate', [AutomationController::class, 'autoValidateEntries'])
        ->name('automation.entries.auto_validate');

    // Statut de l'automatisation
    Route::get('/automation/status', [AutomationController::class, 'getAutomationStatus'])
        ->name('automation.status');
});
