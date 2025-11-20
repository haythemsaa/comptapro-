<?php

namespace App\Console\Commands;

use App\Services\AI\SmartWorkflowService;
use App\Models\Company;
use Illuminate\Console\Command;

class AutoAccountingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'comptapro:auto-accounting {company_id? : ID de l\'entreprise}';

    /**
     * The console command description.
     */
    protected $description = 'Exécute l\'automatisation complète de la comptabilité avec IA';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🤖 ComptaPro - Automatisation Intelligente de la Comptabilité');
        $this->info('==========================================================');
        $this->newLine();

        $companyId = $this->argument('company_id');

        if (!$companyId) {
            // Si aucune entreprise spécifiée, traiter toutes les entreprises tunisiennes
            $companies = Company::where('country', 'TN')->get();

            if ($companies->isEmpty()) {
                $this->error('Aucune entreprise tunisienne trouvée');
                return 1;
            }

            $this->info("Traitement de {$companies->count()} entreprise(s) tunisienne(s)");
            $this->newLine();

            foreach ($companies as $company) {
                $this->processCompany($company->id);
                $this->newLine();
            }

            return 0;
        }

        return $this->processCompany($companyId);
    }

    /**
     * Traite une entreprise
     */
    private function processCompany(int $companyId): int
    {
        try {
            $company = Company::findOrFail($companyId);

            $this->info("Entreprise: {$company->name}");
            $this->info("==========================================================");

            $workflow = new SmartWorkflowService($companyId);

            // Exécuter le pilote automatique
            $this->info('🚀 Activation du pilote automatique...');
            $this->newLine();

            $result = $workflow->enableAutoPilot();

            if ($result['success']) {
                $this->displayResults($result['tasks']);
                $this->newLine();
                $this->info('✅ Pilote automatique exécuté avec succès !');
                return 0;
            } else {
                $this->error('❌ Erreur: ' . $result['error']);
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Erreur fatale: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Affiche les résultats
     */
    private function displayResults(array $tasks): void
    {
        // Documents traités
        if (isset($tasks['document_processing'])) {
            $docs = $tasks['document_processing'];
            $this->info("📄 Documents traités:");
            $this->line("   - Factures: {$docs['invoices_processed']}");
            $this->line("   - Dépenses: {$docs['expenses_processed']}");
            $this->line("   - Écritures générées: {$docs['journal_entries_created']}");
            if (!empty($docs['errors'])) {
                $this->warn("   - Erreurs: " . count($docs['errors']));
            }
            $this->newLine();
        }

        // Validation automatique
        if (isset($tasks['auto_validation'])) {
            $validated = $tasks['auto_validation']['validated'];
            $this->info("✓ Écritures validées automatiquement: {$validated}");
            $this->newLine();
        }

        // Deadlines
        if (isset($tasks['deadline_check'])) {
            $deadlines = $tasks['deadline_check'];
            if ($deadlines['count'] > 0) {
                $this->warn("⏰ {$deadlines['count']} deadline(s) à venir:");
                foreach ($deadlines['alerts'] as $alert) {
                    $this->line("   - {$alert['type']}: {$alert['message']}");
                }
            } else {
                $this->info("✓ Aucune deadline urgente");
            }
            $this->newLine();
        }

        // Anomalies
        if (isset($tasks['anomaly_detection'])) {
            $anomalies = $tasks['anomaly_detection'];
            if ($anomalies['summary']['total_anomalies'] > 0) {
                $this->warn("⚠️  {$anomalies['summary']['total_anomalies']} anomalie(s) détectée(s)");
                foreach (array_slice($anomalies['anomalies'], 0, 3) as $anomaly) {
                    $this->line("   - {$anomaly['type']}: {$anomaly['description']}");
                }
            } else {
                $this->info("✓ Aucune anomalie détectée");
            }
            $this->newLine();
        }

        // Prévision de trésorerie
        if (isset($tasks['cash_flow_forecast'])) {
            $forecast = $tasks['cash_flow_forecast'];
            if (!empty($forecast['predictions'])) {
                $nextWeek = $forecast['predictions'][6] ?? null;
                if ($nextWeek) {
                    $this->info("💰 Trésorerie prévue dans 7 jours: {$nextWeek['predicted_balance']} TND");
                }
            }
            if (!empty($forecast['risks'])) {
                $this->warn("   ⚠️  {count($forecast['risks'])} risque(s) identifié(s)");
            }
            $this->newLine();
        }
    }
}
