<?php

namespace App\Console\Commands;

use App\Services\AI\SmartWorkflowService;
use App\Models\Company;
use Illuminate\Console\Command;

class AutoAnnualWorkflowCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'comptapro:annual-workflow {company_id} {year?}';

    /**
     * The console command description.
     */
    protected $description = 'Exécute le workflow annuel complet (tous les mois + déclaration IS)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🤖 ComptaPro - Workflow Annuel Automatique');
        $this->info('===========================================');
        $this->newLine();

        $companyId = $this->argument('company_id');
        $year = $this->argument('year') ?? now()->year;

        try {
            $company = Company::findOrFail($companyId);

            $this->info("Entreprise: {$company->name}");
            $this->info("Année: {$year}");
            $this->newLine();

            if (!$this->confirm('Cela va traiter 12 mois de données. Continuer?', true)) {
                $this->info('Opération annulée');
                return 0;
            }

            $workflow = new SmartWorkflowService($companyId);

            $progressBar = $this->output->createProgressBar(12);
            $progressBar->start();

            $this->newLine();
            $this->info('Traitement en cours...');
            $this->newLine();

            $result = $workflow->executeCompleteAnnualWorkflow($year);

            $progressBar->finish();
            $this->newLine(2);

            if ($result['success']) {
                $this->info("✅ Workflow annuel terminé avec succès !");
                $this->newLine();

                // Afficher un résumé
                $this->displayAnnualSummary($result);

                return 0;
            } else {
                $this->error('❌ Erreur: ' . ($result['error'] ?? 'Erreur inconnue'));
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Erreur fatale: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Affiche le résumé annuel
     */
    private function displayAnnualSummary(array $result): void
    {
        $this->info('📊 RÉSUMÉ ANNUEL');
        $this->info('================');
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        foreach ($result['monthly_workflows'] as $month => $monthResult) {
            if ($monthResult['success']) {
                $successCount++;
            } else {
                $errorCount++;
                $this->warn("Mois {$month}: Erreur");
            }
        }

        $this->line("Mois traités avec succès: {$successCount}/12");

        if ($errorCount > 0) {
            $this->warn("Mois avec erreurs: {$errorCount}/12");
        }

        $this->newLine();

        // Déclaration IS
        if (isset($result['annual_declarations']['corporate_tax'])) {
            $is = $result['annual_declarations']['corporate_tax'];
            $this->info("📋 Déclaration IS générée:");
            $this->line("   • Résultat fiscal: " . number_format($is->fiscal_result, 3) . " TND");
            $this->line("   • IS dû: " . number_format($is->corporate_tax_due, 3) . " TND");
            $this->line("   • Solde à payer: " . number_format($is->balance_to_pay, 3) . " TND");
        }
    }
}
