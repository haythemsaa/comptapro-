<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome page
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-company/{company}', [DashboardController::class, 'switchCompany'])
        ->name('company.switch');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customers
    Route::resource('customers', CustomerController::class);

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])
        ->name('invoices.mark-sent');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])
        ->name('invoices.mark-paid');

    // Products
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/toggle-active', [ProductController::class, 'toggleActive'])
        ->name('products.toggle-active');

    // Companies
    Route::resource('companies', CompanyController::class);
    Route::get('/companies/{company}/users', [CompanyController::class, 'manageUsers'])
        ->name('companies.users');
    Route::post('/companies/{company}/users', [CompanyController::class, 'addUser'])
        ->name('companies.add-user');
    Route::patch('/companies/{company}/users/{user}', [CompanyController::class, 'updateUserRole'])
        ->name('companies.update-user-role');
    Route::delete('/companies/{company}/users/{user}', [CompanyController::class, 'removeUser'])
        ->name('companies.remove-user');

    // Accounting - Accounts
    Route::get('/accounting/accounts', [AccountingController::class, 'accounts'])
        ->name('accounting.accounts');
    Route::post('/accounting/accounts', [AccountingController::class, 'storeAccount'])
        ->name('accounting.accounts.store');
    Route::patch('/accounting/accounts/{account}', [AccountingController::class, 'updateAccount'])
        ->name('accounting.accounts.update');
    Route::delete('/accounting/accounts/{account}', [AccountingController::class, 'destroyAccount'])
        ->name('accounting.accounts.destroy');

    // Accounting - Journals
    Route::get('/accounting/journals', [AccountingController::class, 'journals'])
        ->name('accounting.journals');
    Route::post('/accounting/journals', [AccountingController::class, 'storeJournal'])
        ->name('accounting.journals.store');
    Route::patch('/accounting/journals/{journal}', [AccountingController::class, 'updateJournal'])
        ->name('accounting.journals.update');

    // Accounting - Journal Entries
    Route::get('/accounting/entries', [AccountingController::class, 'entries'])
        ->name('accounting.entries');
    Route::get('/accounting/entries/create', [AccountingController::class, 'createEntry'])
        ->name('accounting.entries.create');
    Route::post('/accounting/entries', [AccountingController::class, 'storeEntry'])
        ->name('accounting.entries.store');
    Route::get('/accounting/entries/{entry}', [AccountingController::class, 'showEntry'])
        ->name('accounting.entries.show');

    // Accounting - Reports
    Route::get('/accounting/general-ledger', [AccountingController::class, 'generalLedger'])
        ->name('accounting.general-ledger');
    Route::get('/accounting/trial-balance', [AccountingController::class, 'trialBalance'])
        ->name('accounting.trial-balance');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])
        ->name('reports.profit-loss');
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])
        ->name('reports.balance-sheet');
    Route::get('/reports/vat', [ReportController::class, 'vatReport'])
        ->name('reports.vat');
    Route::get('/reports/customer-statement/{customer}', [ReportController::class, 'customerStatement'])
        ->name('reports.customer-statement');
    Route::get('/reports/aged-receivables', [ReportController::class, 'agedReceivables'])
        ->name('reports.aged-receivables');
    Route::get('/reports/sales-by-customer', [ReportController::class, 'salesByCustomer'])
        ->name('reports.sales-by-customer');
    Route::get('/reports/cash-flow', [ReportController::class, 'cashFlow'])
        ->name('reports.cash-flow');
});

require __DIR__.'/auth.php';
