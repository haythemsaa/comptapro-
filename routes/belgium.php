<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Belgium\AutomationControllerBE;

/**
 * Routes pour ComptaPro Belgique
 * Toutes les fonctionnalités spécifiques à la Belgique
 */

Route::middleware(['auth', 'country:BE'])->prefix('belgium')->name('belgium.')->group(function () {

    // ========================================
    // DASHBOARD & AUTOMATISATION
    // ========================================
    Route::get('/automation', [AutomationControllerBE::class, 'dashboard'])
        ->name('automation.dashboard');

    Route::get('/automation/stats', [AutomationControllerBE::class, 'stats'])
        ->name('automation.stats');

    // ========================================
    // UPLOAD & TRAITEMENT DOCUMENTS
    // ========================================
    Route::post('/automation/upload', [AutomationControllerBE::class, 'uploadDocument'])
        ->name('automation.upload');

    // ========================================
    // TVA (DÉCLARATIONS)
    // ========================================
    Route::prefix('vat')->name('vat.')->group(function () {
        // Liste des déclarations
        Route::get('/', [AutomationControllerBE::class, 'vatDeclarations'])
            ->name('index');

        // Générer nouvelle déclaration
        Route::post('/generate', [AutomationControllerBE::class, 'generateVATDeclaration'])
            ->name('generate');

        // Voir une déclaration
        Route::get('/{id}', [AutomationControllerBE::class, 'showVATDeclaration'])
            ->name('show');

        // Soumettre une déclaration
        Route::post('/{id}/submit', [AutomationControllerBE::class, 'submitVATDeclaration'])
            ->name('submit');

        // Télécharger PDF
        Route::get('/{id}/pdf', [AutomationControllerBE::class, 'downloadVATPDF'])
            ->name('pdf');
    });

    // ========================================
    // PAIE & ONSS
    // ========================================
    Route::prefix('payroll')->name('payroll.')->group(function () {
        // Dashboard paie
        Route::get('/', [AutomationControllerBE::class, 'payroll'])
            ->name('index');

        // Générer paie pour un employé
        Route::post('/generate', [AutomationControllerBE::class, 'generatePayroll'])
            ->name('generate');

        // Générer toutes les paies du mois
        Route::post('/generate-all', [AutomationControllerBE::class, 'generateAllPayrolls'])
            ->name('generate-all');

        // Voir une fiche de paie
        Route::get('/{id}', [AutomationControllerBE::class, 'showPayroll'])
            ->name('show');

        // Télécharger fiche de paie PDF
        Route::get('/{id}/pdf', [AutomationControllerBE::class, 'downloadPayrollPDF'])
            ->name('pdf');
    });

    // ONSS
    Route::prefix('onss')->name('onss.')->group(function () {
        // Liste déclarations ONSS
        Route::get('/', [AutomationControllerBE::class, 'onssDeclarations'])
            ->name('index');

        // Générer déclaration ONSS
        Route::post('/generate', [AutomationControllerBE::class, 'generateONSSDeclaration'])
            ->name('generate');

        // Télécharger déclaration
        Route::get('/{id}/download', [AutomationControllerBE::class, 'downloadONSSDeclaration'])
            ->name('download');
    });

    // ========================================
    // IMPÔT DES SOCIÉTÉS
    // ========================================
    Route::prefix('company-tax')->name('company-tax.')->group(function () {
        // Liste déclarations IS
        Route::get('/', [AutomationControllerBE::class, 'companyTax'])
            ->name('index');

        // Générer déclaration IS
        Route::post('/generate', [AutomationControllerBE::class, 'generateCompanyTaxDeclaration'])
            ->name('generate');

        // Voir déclaration
        Route::get('/{id}', [AutomationControllerBE::class, 'showCompanyTax'])
            ->name('show');

        // Optimisations fiscales IA
        Route::get('/{id}/optimize', [AutomationControllerBE::class, 'optimizeCompanyTax'])
            ->name('optimize');

        // Télécharger PDF
        Route::get('/{id}/pdf', [AutomationControllerBE::class, 'downloadCompanyTaxPDF'])
            ->name('pdf');
    });

    // ========================================
    // PLAN COMPTABLE (PCMN)
    // ========================================
    Route::prefix('chart-of-accounts')->name('coa.')->group(function () {
        // Liste des comptes
        Route::get('/', [AutomationControllerBE::class, 'chartOfAccounts'])
            ->name('index');

        // Recherche
        Route::get('/search', [AutomationControllerBE::class, 'searchAccounts'])
            ->name('search');

        // Arbre hiérarchique
        Route::get('/tree', [AutomationControllerBE::class, 'accountsTree'])
            ->name('tree');
    });

    // ========================================
    // EMPLOYÉS
    // ========================================
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [AutomationControllerBE::class, 'employees'])->name('index');
        Route::post('/', [AutomationControllerBE::class, 'storeEmployee'])->name('store');
        Route::get('/{id}', [AutomationControllerBE::class, 'showEmployee'])->name('show');
        Route::put('/{id}', [AutomationControllerBE::class, 'updateEmployee'])->name('update');
        Route::delete('/{id}', [AutomationControllerBE::class, 'deleteEmployee'])->name('delete');
    });

    // ========================================
    // RAPPORTS & ÉTATS FINANCIERS
    // ========================================
    Route::prefix('reports')->name('reports.')->group(function () {
        // Bilan
        Route::get('/balance-sheet', [AutomationControllerBE::class, 'balanceSheet'])
            ->name('balance-sheet');

        // Compte de résultat
        Route::get('/income-statement', [AutomationControllerBE::class, 'incomeStatement'])
            ->name('income-statement');

        // Flux de trésorerie
        Route::get('/cash-flow', [AutomationControllerBE::class, 'cashFlow'])
            ->name('cash-flow');

        // Grand livre
        Route::get('/general-ledger', [AutomationControllerBE::class, 'generalLedger'])
            ->name('general-ledger');

        // Balance
        Route::get('/trial-balance', [AutomationControllerBE::class, 'trialBalance'])
            ->name('trial-balance');
    });

    // ========================================
    // ÉCHÉANCES & CALENDRIER
    // ========================================
    Route::get('/deadlines', [AutomationControllerBE::class, 'deadlines'])
        ->name('deadlines');

    Route::get('/calendar', [AutomationControllerBE::class, 'calendar'])
        ->name('calendar');

    // ========================================
    // ASSISTANT IA
    // ========================================
    Route::prefix('ai')->name('ai.')->group(function () {
        // Chat avec assistant
        Route::post('/chat', [AutomationControllerBE::class, 'aiChat'])
            ->name('chat');

        // Analyse document
        Route::post('/analyze', [AutomationControllerBE::class, 'analyzeDocument'])
            ->name('analyze');

        // Suggestions d'optimisation
        Route::get('/suggestions', [AutomationControllerBE::class, 'aiSuggestions'])
            ->name('suggestions');
    });
});

/**
 * Routes API pour Belgique
 */
Route::middleware(['auth:sanctum', 'country:BE'])->prefix('api/v1/belgium')->name('api.belgium.')->group(function () {

    // Statistiques
    Route::get('/stats', [AutomationControllerBE::class, 'apiStats']);

    // TVA
    Route::apiResource('vat-declarations', AutomationControllerBE::class . '@apiVATDeclarations');

    // Paie
    Route::apiResource('payrolls', AutomationControllerBE::class . '@apiPayrolls');

    // ONSS
    Route::apiResource('onss-declarations', AutomationControllerBE::class . '@apiONSSDeclarations');

    // IS
    Route::apiResource('company-taxes', AutomationControllerBE::class . '@apiCompanyTaxes');

    // Employés
    Route::apiResource('employees', AutomationControllerBE::class . '@apiEmployees');

    // Plan comptable
    Route::get('/chart-of-accounts', [AutomationControllerBE::class, 'apiChartOfAccounts']);
    Route::get('/chart-of-accounts/search', [AutomationControllerBE::class, 'apiSearchAccounts']);
});
