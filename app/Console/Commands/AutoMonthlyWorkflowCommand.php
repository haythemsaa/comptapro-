<?php

namespace App\Console\Commands;

use App\Services\AI\SmartWorkflowService;
use App\Models\Company;
use Illuminate\Console\Command;

class AutoMonthlyWorkflowCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'comptapro:monthly-workflow {company_id} {year?} {month?}';

    /**
     * The console command description.
     */
    protected $description = 'Exécute le workflow mensuel complet (comptabilité, états financiers, déclarations)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🤖 ComptaPro - Workflow Mensuel Automatique');
        $this->info('===========================================');
        $this->newLine();

        $companyId = $this->argument('company_id');
        $year = $this->argument('year') ?? now()->year;
        $month = $this->argument('month') ?? now()->month;

        try {
            $company = Company::findOrFail($companyId);

            $this->info("Entreprise: {$company->name}");
            $this->info("Période: {$year}-{$month}");
            $this->newLine();

            $workflow = new SmartWorkflowService($companyId);

            $progressBar = $this->output->createProgressBar(6);
            $progressBar->setFormat('verbose');
            $progressBar->start();

            // Exécuter le workflow complet
            $this->info('Exécution du workflow complet...');
            $this->newLine();

            $result = $workflow->executeCompleteMonthlyWorkflow($year, $month);

            $progressBar->finish();
            $this->newLine(2);

            if ($result['success']) {
                $this->displayWorkflowResults($result);
                $this->newLine();
                $this->info("✅ Workflow mensuel terminé en {$result['duration_seconds']}s");
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
     * Affiche les résultats du workflow
     */
    private function displayWorkflowResults(array $result): void
    {
        $this->info('📊 RÉSULTATS DU WORKFLOW');
        $this->info('=========================');
        $this->newLine();

        // Documents traités
        if (isset($result['steps']['process_documents']['data'])) {
            $docs = $result['steps']['process_documents']['data'];
            $this->line("📄 Documents traités:");
            $this->line("   • Factures: {$docs['invoices_processed']}");
            $this->line("   • Dépenses: {$docs['expenses_processed']}");
            $this->line("   • Écritures: {$docs['journal_entries_created']}");
            $this->newLine();
        }

        // États financiers
        if (isset($result['steps']['financial_reports']['data']['income_statement'])) {
            $income = $result['steps']['financial_reports']['data']['income_statement'];
            $this->line("💼 États financiers:");
            $this->line("   • CA: " . number_format($income['produits']['total_produits_exploitation'], 3) . " TND");
            $this->line("   • Résultat net: " . number_format($income['resultats']['resultat_net'], 3) . " TND");
            $this->line("   • Marge nette: " . number_format($income['marges']['marge_nette_pct'], 2) . "%");
            $this->newLine();
        }

        // Déclarations
        if (isset($result['steps']['tax_declarations']['data'])) {
            $decl = $result['steps']['tax_declarations']['data'];
            $this->line("📋 Déclarations générées:");

            if ($decl['vat_declaration']) {
                $vat = $decl['vat_declaration'];
                $this->line("   • TVA: " . number_format($vat->vat_to_pay, 3) . " TND à payer");
            }

            if ($decl['cnss_declaration']) {
                $cnss = $decl['cnss_declaration'];
                $this->line("   • CNSS: " . number_format($cnss->total_cnss, 3) . " TND");
            }
            $this->newLine();
        }

        // Deadlines
        if (isset($result['steps']['check_deadlines']['data']['alerts'])) {
            $alerts = $result['steps']['check_deadlines']['data']['alerts'];
            if (!empty($alerts)) {
                $this->warn("⏰ Deadlines à venir:");
                foreach ($alerts as $alert) {
                    $this->line("   • {$alert['message']}");
                }
                $this->newLine();
            }
        }

        // Synthèse
        if (isset($result['steps']['generate_summary']['data'])) {
            $summary = $result['steps']['generate_summary']['data'];
            if (!empty($summary['next_actions'])) {
                $this->info("📌 Actions recommandées:");
                foreach ($summary['next_actions'] as $action) {
                    $this->line("   • {$action}");
                }
            }
        }
    }
}
