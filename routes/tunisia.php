<?php

use App\Http\Controllers\AI\AIAssistantController;
use App\Http\Controllers\Banking\BankReconciliationController;
use App\Http\Controllers\Payroll\PayrollController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Spécifiques Tunisie
|--------------------------------------------------------------------------
|
| Routes pour les fonctionnalités spécifiques au marché tunisien:
| - Paie et CNSS
| - Déclarations fiscales
| - El Fatoora
| - Assistant IA
| - Rapprochement bancaire
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // ========================================
    // PAIE ET CNSS
    // ========================================
    Route::prefix('payroll')->name('payroll.')->group(function () {
        // Employés
        Route::get('/employees', [PayrollController::class, 'employees'])->name('employees');
        Route::post('/employees', [PayrollController::class, 'storeEmployee'])->name('employees.store');
        Route::get('/employees/{employee}', [PayrollController::class, 'showEmployee'])->name('employees.show');
        Route::put('/employees/{employee}', [PayrollController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{employee}', [PayrollController::class, 'destroyEmployee'])->name('employees.destroy');

        // Bulletins de paie
        Route::get('/payslips', [PayrollController::class, 'payslips'])->name('payslips');
        Route::post('/payslips/generate', [PayrollController::class, 'generatePayslip'])->name('payslips.generate');
        Route::get('/payslips/{payslip}', [PayrollController::class, 'showPayslip'])->name('payslips.show');
        Route::get('/payslips/{payslip}/pdf', [PayrollController::class, 'downloadPayslipPDF'])->name('payslips.pdf');
        Route::post('/payslips/{payslip}/validate', [PayrollController::class, 'validatePayslip'])->name('payslips.validate');
        Route::post('/payslips/{payslip}/pay', [PayrollController::class, 'markAsPaid'])->name('payslips.pay');

        // Déclarations CNSS
        Route::get('/cnss-declarations', [PayrollController::class, 'cnssDeclarations'])->name('cnss-declarations');
        Route::post('/cnss-declarations/generate', [PayrollController::class, 'generateCNSSDeclaration'])->name('cnss-declarations.generate');
        Route::get('/cnss-declarations/{declaration}', [PayrollController::class, 'showCNSSDeclaration'])->name('cnss-declarations.show');
        Route::post('/cnss-declarations/{declaration}/submit', [PayrollController::class, 'submitCNSSDeclaration'])->name('cnss-declarations.submit');
        Route::get('/cnss-declarations/{declaration}/export', [PayrollController::class, 'exportCNSSDeclaration'])->name('cnss-declarations.export');

        // Congés
        Route::get('/leave-requests', [PayrollController::class, 'leaveRequests'])->name('leave-requests');
        Route::post('/leave-requests', [PayrollController::class, 'storeLeaveRequest'])->name('leave-requests.store');
        Route::post('/leave-requests/{request}/approve', [PayrollController::class, 'approveLeaveRequest'])->name('leave-requests.approve');
        Route::post('/leave-requests/{request}/reject', [PayrollController::class, 'rejectLeaveRequest'])->name('leave-requests.reject');

        // Simulation
        Route::post('/simulate', [PayrollController::class, 'simulatePayroll'])->name('simulate');
    });

    // ========================================
    // ASSISTANT IA
    // ========================================
    Route::prefix('ai')->name('ai.')->group(function () {
        // Assistant conversationnel
        Route::get('/assistant', [AIAssistantController::class, 'index'])->name('assistant');
        Route::post('/assistant/ask', [AIAssistantController::class, 'ask'])->name('assistant.ask');

        // Analyses IA
        Route::post('/analyze-finances', [AIAssistantController::class, 'analyzeFinances'])->name('analyze-finances');
        Route::post('/tax-optimizations', [AIAssistantController::class, 'suggestTaxOptimizations'])->name('tax-optimizations');
        Route::post('/categorize-transaction', [AIAssistantController::class, 'categorizeTransaction'])->name('categorize-transaction');

        // OCR
        Route::post('/ocr/extract', [AIAssistantController::class, 'extractInvoiceData'])->name('ocr.extract');

        // Prédictions
        Route::post('/cash-flow/predict', [AIAssistantController::class, 'predictCashFlow'])->name('cash-flow.predict');
        Route::post('/anomalies/detect', [AIAssistantController::class, 'detectAnomalies'])->name('anomalies.detect');

        // Dashboard IA
        Route::get('/dashboard', [AIAssistantController::class, 'dashboard'])->name('dashboard');
    });

    // ========================================
    // RAPPROCHEMENT BANCAIRE
    // ========================================
    Route::prefix('banking')->name('banking.')->group(function () {
        // Comptes bancaires
        Route::get('/accounts', [BankReconciliationController::class, 'accounts'])->name('accounts');
        Route::post('/accounts', [BankReconciliationController::class, 'storeAccount'])->name('accounts.store');

        // Import relevés
        Route::post('/statements/import', [BankReconciliationController::class, 'importStatement'])->name('statements.import');

        // Rapprochement
        Route::get('/reconciliation/{account}', [BankReconciliationController::class, 'reconciliation'])->name('reconciliation');
        Route::post('/reconciliation/perform', [BankReconciliationController::class, 'performReconciliation'])->name('reconciliation.perform');
        Route::post('/reconciliation/manual-match', [BankReconciliationController::class, 'manualMatch'])->name('reconciliation.manual-match');
        Route::post('/reconciliation/unmatch', [BankReconciliationController::class, 'unmatch'])->name('reconciliation.unmatch');
        Route::post('/reconciliation/{reconciliation}/approve', [BankReconciliationController::class, 'approve'])->name('reconciliation.approve');

        // Statistiques
        Route::get('/reconciliation/{account}/stats', [BankReconciliationController::class, 'stats'])->name('reconciliation.stats');
    });

    // ========================================
    // DÉCLARATIONS FISCALES TUNISIENNES
    // ========================================
    Route::prefix('tax')->name('tax.')->group(function () {
        // TVA
        Route::get('/vat', [TaxController::class, 'vatDeclarations'])->name('vat');
        Route::post('/vat/generate', [TaxController::class, 'generateVATDeclaration'])->name('vat.generate');
        Route::get('/vat/{declaration}', [TaxController::class, 'showVATDeclaration'])->name('vat.show');
        Route::get('/vat/{declaration}/teif', [TaxController::class, 'exportToTEIF'])->name('vat.teif');
        Route::post('/vat/{declaration}/submit', [TaxController::class, 'submitVATDeclaration'])->name('vat.submit');

        // IS (Impôt sur les Sociétés)
        Route::get('/corporate-tax', [TaxController::class, 'corporateTaxDeclarations'])->name('corporate-tax');
        Route::post('/corporate-tax/generate', [TaxController::class, 'generateCorporateTaxDeclaration'])->name('corporate-tax.generate');
        Route::get('/corporate-tax/{declaration}', [TaxController::class, 'showCorporateTaxDeclaration'])->name('corporate-tax.show');

        // Acomptes IS
        Route::get('/corporate-tax/{declaration}/advances', [TaxController::class, 'advances'])->name('corporate-tax.advances');
        Route::post('/corporate-tax/advances/{advance}/pay', [TaxController::class, 'payAdvance'])->name('corporate-tax.advances.pay');

        // TFP
        Route::get('/tfp', [TaxController::class, 'tfpDeclarations'])->name('tfp');
        Route::post('/tfp/generate', [TaxController::class, 'generateTFPDeclaration'])->name('tfp.generate');

        // Retenues à la source
        Route::get('/withholding', [TaxController::class, 'withholdingDeclarations'])->name('withholding');
        Route::post('/withholding/generate', [TaxController::class, 'generateWithholdingDeclaration'])->name('withholding.generate');

        // Calendrier fiscal
        Route::get('/calendar', [TaxController::class, 'taxCalendar'])->name('calendar');
    });

    // ========================================
    // EL FATOORA (Facturation Électronique)
    // ========================================
    Route::prefix('elfatoora')->name('elfatoora.')->group(function () {
        // Signature et transmission
        Route::post('/invoices/{invoice}/sign', [ElFatooraController::class, 'signInvoice'])->name('invoices.sign');
        Route::post('/invoices/{invoice}/transmit', [ElFatooraController::class, 'transmitInvoice'])->name('invoices.transmit');
        Route::post('/invoices/{invoice}/process', [ElFatooraController::class, 'processInvoice'])->name('invoices.process');

        // Validation et statut
        Route::get('/invoices/{invoice}/status', [ElFatooraController::class, 'checkStatus'])->name('invoices.status');
        Route::post('/invoices/{invoice}/validate', [ElFatooraController::class, 'validateInvoice'])->name('invoices.validate');

        // Annulation
        Route::post('/invoices/{invoice}/cancel', [ElFatooraController::class, 'cancelInvoice'])->name('invoices.cancel');

        // Archive
        Route::get('/invoices/{invoice}/archive', [ElFatooraController::class, 'archiveInvoice'])->name('invoices.archive');

        // Test connexion
        Route::get('/test-connection', [ElFatooraController::class, 'testConnection'])->name('test-connection');
    });
});

// ========================================
// API PUBLIQUE (avec authentification token)
// ========================================
Route::prefix('api/v1')->middleware(['auth:sanctum'])->group(function () {
    // Endpoints pour intégrations externes
    Route::post('/payroll/calculate', [PayrollController::class, 'calculateAPI']);
    Route::post('/ai/ask', [AIAssistantController::class, 'askAPI']);
    Route::post('/vat/calculate', [TaxController::class, 'calculateVATAPI']);
});
