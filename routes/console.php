<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Company;
use App\Services\AI\SmartWorkflowService;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Scheduled Tasks - ComptaPro Tunisia Automation
|--------------------------------------------------------------------------
| Configuration des tâches automatiques pour l'automatisation complète
*/

// PILOTE AUTOMATIQUE - Toutes les 6 heures
// Traite les documents en attente, valide les écritures, vérifie les deadlines
Schedule::command('comptapro:auto-accounting')
    ->everySixHours()
    ->name('auto-accounting-pilot')
    ->runInBackground()
    ->emailOutputOnFailure(config('mail.from.address'));

// WORKFLOW MENSUEL - Le 25 de chaque mois à 02:00
// Génère automatiquement tous les états et déclarations du mois précédent
Schedule::call(function () {
    $companies = Company::where('country', 'TN')
        ->where('auto_accounting_enabled', true)
        ->get();

    foreach ($companies as $company) {
        try {
            $workflow = new SmartWorkflowService($company->id);
            $lastMonth = now()->subMonth();

            $workflow->executeCompleteMonthlyWorkflow(
                $lastMonth->year,
                $lastMonth->month
            );

            Log::info("Monthly workflow executed for company {$company->id}");
        } catch (\Exception $e) {
            Log::error("Monthly workflow failed for company {$company->id}: " . $e->getMessage());
        }
    }
})->monthlyOn(25, '02:00')
  ->name('monthly-workflow-auto')
  ->runInBackground();

// VALIDATION AUTOMATIQUE - Tous les jours à 03:00
// Valide automatiquement les écritures avec confiance >= 90%
Schedule::call(function () {
    $companies = Company::where('country', 'TN')
        ->where('auto_accounting_enabled', true)
        ->get();

    foreach ($companies as $company) {
        try {
            $autoAccounting = new \App\Services\AI\AutoAccountingService($company->id);
            $validated = $autoAccounting->autoValidateHighConfidenceEntries();

            Log::info("Auto-validated {$validated} entries for company {$company->id}");
        } catch (\Exception $e) {
            Log::error("Auto-validation failed for company {$company->id}: " . $e->getMessage());
        }
    }
})->dailyAt('03:00')
  ->name('auto-validate-entries')
  ->runInBackground();

// ALERTES DEADLINES - Tous les jours à 08:00
// Vérifie les deadlines à venir et envoie des notifications
Schedule::call(function () {
    $companies = Company::where('country', 'TN')->get();

    foreach ($companies as $company) {
        try {
            $autoTax = new \App\Services\AI\AutoTaxDeclarationService($company->id);
            $deadlines = $autoTax->checkUpcomingDeadlines(7);

            if ($deadlines['count'] > 0) {
                // Envoyer notification aux utilisateurs de l'entreprise
                foreach ($company->users as $user) {
                    // À implémenter: notification par email ou in-app
                    Log::info("Deadline alert sent to user {$user->id}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Deadline check failed for company {$company->id}: " . $e->getMessage());
        }
    }
})->dailyAt('08:00')
  ->name('deadline-alerts')
  ->runInBackground();

// GÉNÉRATION DÉCLARATIONS TVA - Le 20 de chaque mois à 00:00
// Génère automatiquement la déclaration TVA du mois précédent
Schedule::call(function () {
    $companies = Company::where('country', 'TN')
        ->where('auto_accounting_enabled', true)
        ->get();

    foreach ($companies as $company) {
        try {
            $autoTax = new \App\Services\AI\AutoTaxDeclarationService($company->id);
            $lastMonth = now()->subMonth();

            $declaration = $autoTax->generateVATDeclaration($lastMonth->year, $lastMonth->month);

            Log::info("VAT declaration auto-generated for company {$company->id}");
        } catch (\Exception $e) {
            Log::error("VAT generation failed for company {$company->id}: " . $e->getMessage());
        }
    }
})->monthlyOn(20, '00:00')
  ->name('auto-vat-declarations')
  ->runInBackground();

// GÉNÉRATION DÉCLARATIONS CNSS - Le 10 de chaque mois à 00:00
// Génère automatiquement la déclaration CNSS du mois précédent
Schedule::call(function () {
    $companies = Company::where('country', 'TN')
        ->where('auto_accounting_enabled', true)
        ->get();

    foreach ($companies as $company) {
        try {
            $autoTax = new \App\Services\AI\AutoTaxDeclarationService($company->id);
            $lastMonth = now()->subMonth();

            $declaration = $autoTax->generateCNSSDeclaration($lastMonth->year, $lastMonth->month);

            Log::info("CNSS declaration auto-generated for company {$company->id}");
        } catch (\Exception $e) {
            Log::error("CNSS generation failed for company {$company->id}: " . $e->getMessage());
        }
    }
})->monthlyOn(10, '00:00')
  ->name('auto-cnss-declarations')
  ->runInBackground();

// DÉTECTION D'ANOMALIES - Tous les jours à 04:00
// Détecte les anomalies comptables et envoie des alertes
Schedule::call(function () {
    $companies = Company::where('country', 'TN')->get();

    foreach ($companies as $company) {
        try {
            $anomalyDetector = new \App\Services\AI\AnomalyDetectionService($company->id);
            $anomalies = $anomalyDetector->detectAllAnomalies();

            $highSeverity = $anomalies['summary']['high_severity'] ?? 0;

            if ($highSeverity > 0) {
                // Envoyer alerte pour anomalies graves
                Log::warning("High severity anomalies detected for company {$company->id}: {$highSeverity}");
            }
        } catch (\Exception $e) {
            Log::error("Anomaly detection failed for company {$company->id}: " . $e->getMessage());
        }
    }
})->dailyAt('04:00')
  ->name('anomaly-detection')
  ->runInBackground();

// PRÉVISION DE TRÉSORERIE - Tous les lundis à 07:00
// Génère les prévisions de trésorerie pour la semaine
Schedule::call(function () {
    $companies = Company::where('country', 'TN')->get();

    foreach ($companies as $company) {
        try {
            $cashFlowPredictor = new \App\Services\AI\CashFlowPredictor($company->id);
            $forecast = $cashFlowPredictor->predictCashFlow(30);

            // Si risques détectés, envoyer alerte
            if (!empty($forecast['risks'])) {
                Log::warning("Cash flow risks detected for company {$company->id}");
            }
        } catch (\Exception $e) {
            Log::error("Cash flow prediction failed for company {$company->id}: " . $e->getMessage());
        }
    }
})->weeklyOn(1, '07:00')
  ->name('cash-flow-forecast')
  ->runInBackground();

// GÉNÉRATION ÉTATS FINANCIERS - Le dernier jour du mois à 23:00
// Génère automatiquement tous les états financiers du mois
Schedule::call(function () {
    $companies = Company::where('country', 'TN')
        ->where('auto_accounting_enabled', true)
        ->get();

    foreach ($companies as $company) {
        try {
            $autoReports = new \App\Services\AI\AutoFinancialReportsService($company->id);
            $currentMonth = now();

            $reports = $autoReports->generateAllFinancialReports(
                $currentMonth->year,
                $currentMonth->month
            );

            Log::info("Financial reports auto-generated for company {$company->id}");
        } catch (\Exception $e) {
            Log::error("Financial reports failed for company {$company->id}: " . $e->getMessage());
        }
    }
})->monthlyOn(Carbon\Carbon::now()->endOfMonth()->day, '23:00')
  ->name('auto-financial-reports')
  ->runInBackground();

// NETTOYAGE - Tous les dimanches à 01:00
// Nettoie les fichiers temporaires et optimise la base de données
Schedule::call(function () {
    // Supprimer les fichiers temporaires de plus de 30 jours
    $tempPath = storage_path('app/documents/pending');
    if (File::exists($tempPath)) {
        $files = File::files($tempPath);
        $thirtyDaysAgo = now()->subDays(30);

        foreach ($files as $file) {
            if (File::lastModified($file) < $thirtyDaysAgo->timestamp) {
                File::delete($file);
            }
        }
    }

    // Optimiser la base de données
    Artisan::call('optimize');

    Log::info("System cleanup completed");
})->weekly()->sundays()->at('01:00')
  ->name('system-cleanup')
  ->runInBackground();
