<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JournalEntryController;
use App\Http\Controllers\Admin\ChartOfAccountsController;
use App\Http\Controllers\Admin\AccountingReportsController;
use App\Http\Controllers\Admin\Tunisia\EmployeeTNController;
use App\Http\Controllers\Admin\Tunisia\PayrollTNController;
use App\Http\Controllers\Admin\Tunisia\TaxTNController;
use App\Http\Controllers\Admin\Belgium\EmployeeBEController;
use App\Http\Controllers\Admin\Belgium\PayrollBEController;
use App\Http\Controllers\Admin\Belgium\TaxBEController;

/**
 * Routes Admin - ComptaPro (Tunisia & Belgium)
 *
 * Toutes les routes admin sont protégées par le middleware 'auth'
 * et préfixées par '/admin'
 */

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // ============================================================
    // DASHBOARD
    // ============================================================
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats/realtime', [DashboardController::class, 'realtimeStats'])->name('stats.realtime');

    // ============================================================
    // GESTION DES ENTREPRISES
    // ============================================================
    Route::resource('companies', CompanyController::class);
    Route::post('companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])
        ->name('companies.toggle-status');

    // ============================================================
    // GESTION DES UTILISATEURS
    // ============================================================
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password');
    Route::get('companies/{company}/users', [UserController::class, 'byCompany'])
        ->name('users.by-company');

    // ============================================================
    // COMPTABILITÉ - ÉCRITURES
    // ============================================================
    Route::prefix('accounting')->name('accounting.')->group(function () {

        // Écritures comptables
        Route::resource('journal-entries', JournalEntryController::class);
        Route::get('companies/{company}/accounts', [JournalEntryController::class, 'getAccountsByCompany'])
            ->name('journal-entries.accounts-by-company');

        // Plan comptable (PCN / PCMN)
        Route::prefix('chart-of-accounts')->name('chart-of-accounts.')->group(function () {
            Route::get('/', [ChartOfAccountsController::class, 'index'])->name('index');
            Route::get('/create', [ChartOfAccountsController::class, 'create'])->name('create');
            Route::post('/', [ChartOfAccountsController::class, 'store'])->name('store');
            Route::get('/{accountNumber}', [ChartOfAccountsController::class, 'show'])->name('show');
            Route::get('/{accountNumber}/edit', [ChartOfAccountsController::class, 'edit'])->name('edit');
            Route::put('/{accountNumber}', [ChartOfAccountsController::class, 'update'])->name('update');
            Route::post('/{accountNumber}/toggle-status', [ChartOfAccountsController::class, 'toggleStatus'])
                ->name('toggle-status');
            Route::get('/export', [ChartOfAccountsController::class, 'export'])->name('export');
        });

        // Rapports financiers
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AccountingReportsController::class, 'index'])->name('index');
            Route::get('/dashboard', [AccountingReportsController::class, 'dashboard'])->name('dashboard');
            Route::get('/balance-sheet', [AccountingReportsController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/income-statement', [AccountingReportsController::class, 'incomeStatement'])->name('income-statement');
            Route::get('/trial-balance', [AccountingReportsController::class, 'trialBalance'])->name('trial-balance');
            Route::get('/general-ledger', [AccountingReportsController::class, 'generalLedger'])->name('general-ledger');
            Route::post('/export-pdf', [AccountingReportsController::class, 'exportPDF'])->name('export-pdf');
            Route::post('/export-excel', [AccountingReportsController::class, 'exportExcel'])->name('export-excel');
        });
    });

    // ============================================================
    // TUNISIA - EMPLOYEES, PAYROLL, TAXES
    // ============================================================
    Route::prefix('tunisia')->name('tunisia.')->group(function () {

        // Employés
        Route::resource('employees', EmployeeTNController::class);
        Route::post('employees/{employee}/toggle-status', [EmployeeTNController::class, 'toggleStatus'])
            ->name('employees.toggle-status');
        Route::post('employees/{employee}/calculate-payroll', [EmployeeTNController::class, 'calculatePayroll'])
            ->name('employees.calculate-payroll');

        // Paie
        Route::resource('payrolls', PayrollTNController::class);
        Route::post('payrolls/{payroll}/validate', [PayrollTNController::class, 'validate'])
            ->name('payrolls.validate');
        Route::post('payrolls/{payroll}/mark-as-paid', [PayrollTNController::class, 'markAsPaid'])
            ->name('payrolls.mark-as-paid');
        Route::get('payrolls/{payroll}/download-pdf', [PayrollTNController::class, 'downloadPDF'])
            ->name('payrolls.download-pdf');
        Route::get('companies/{company}/employees', [PayrollTNController::class, 'getEmployeesByCompany'])
            ->name('payrolls.employees-by-company');

        // Déclarations fiscales
        Route::prefix('taxes')->name('taxes.')->group(function () {
            Route::get('/', [TaxTNController::class, 'index'])->name('index');

            // TVA
            Route::prefix('vat')->name('vat.')->group(function () {
                Route::get('/', [TaxTNController::class, 'vatIndex'])->name('index');
                Route::get('/create', [TaxTNController::class, 'vatCreate'])->name('create');
                Route::post('/', [TaxTNController::class, 'vatStore'])->name('store');
                Route::get('/{declaration}', [TaxTNController::class, 'vatShow'])->name('show');
                Route::post('/{declaration}/submit', [TaxTNController::class, 'vatSubmit'])->name('submit');
            });

            // Impôt sur les Sociétés
            Route::prefix('is')->name('is.')->group(function () {
                Route::get('/', [TaxTNController::class, 'isIndex'])->name('index');
                Route::get('/create', [TaxTNController::class, 'isCreate'])->name('create');
                Route::post('/', [TaxTNController::class, 'isStore'])->name('store');
                Route::get('/{declaration}', [TaxTNController::class, 'isShow'])->name('show');
                Route::post('/{declaration}/submit', [TaxTNController::class, 'isSubmit'])->name('submit');
            });

            Route::delete('/destroy', [TaxTNController::class, 'destroy'])->name('destroy');
        });
    });

    // ============================================================
    // BELGIUM - EMPLOYEES, PAYROLL, TAXES
    // ============================================================
    Route::prefix('belgium')->name('belgium.')->group(function () {

        // Employés
        Route::resource('employees', EmployeeBEController::class);
        Route::post('employees/{employee}/toggle-status', [EmployeeBEController::class, 'toggleStatus'])
            ->name('employees.toggle-status');
        Route::post('employees/{employee}/calculate-payroll', [EmployeeBEController::class, 'calculatePayroll'])
            ->name('employees.calculate-payroll');

        // Paie
        Route::resource('payrolls', PayrollBEController::class);
        Route::post('payrolls/{payroll}/validate', [PayrollBEController::class, 'validate'])
            ->name('payrolls.validate');
        Route::post('payrolls/{payroll}/mark-as-paid', [PayrollBEController::class, 'markAsPaid'])
            ->name('payrolls.mark-as-paid');
        Route::get('payrolls/{payroll}/download-pdf', [PayrollBEController::class, 'downloadPDF'])
            ->name('payrolls.download-pdf');
        Route::get('companies/{company}/employees', [PayrollBEController::class, 'getEmployeesByCompany'])
            ->name('payrolls.employees-by-company');
        Route::post('payrolls/export-dimona', [PayrollBEController::class, 'exportDimona'])
            ->name('payrolls.export-dimona');
        Route::post('payrolls/export-dmfa', [PayrollBEController::class, 'exportDmfa'])
            ->name('payrolls.export-dmfa');

        // Déclarations fiscales
        Route::prefix('taxes')->name('taxes.')->group(function () {
            Route::get('/', [TaxBEController::class, 'index'])->name('index');

            // TVA
            Route::prefix('vat')->name('vat.')->group(function () {
                Route::get('/', [TaxBEController::class, 'vatIndex'])->name('index');
                Route::get('/create', [TaxBEController::class, 'vatCreate'])->name('create');
                Route::post('/', [TaxBEController::class, 'vatStore'])->name('store');
                Route::get('/{declaration}', [TaxBEController::class, 'vatShow'])->name('show');
                Route::post('/{declaration}/submit', [TaxBEController::class, 'vatSubmit'])->name('submit');
                Route::get('/{declaration}/export-intervat', [TaxBEController::class, 'vatExportIntervat'])
                    ->name('export-intervat');
            });

            // Impôt des Sociétés
            Route::prefix('is')->name('is.')->group(function () {
                Route::get('/', [TaxBEController::class, 'isIndex'])->name('index');
                Route::get('/create', [TaxBEController::class, 'isCreate'])->name('create');
                Route::post('/', [TaxBEController::class, 'isStore'])->name('store');
                Route::get('/{declaration}', [TaxBEController::class, 'isShow'])->name('show');
                Route::post('/{declaration}/submit', [TaxBEController::class, 'isSubmit'])->name('submit');
                Route::get('/{declaration}/export-biztax', [TaxBEController::class, 'isExportBiztax'])
                    ->name('export-biztax');
            });

            Route::delete('/destroy', [TaxBEController::class, 'destroy'])->name('destroy');
        });
    });
});
