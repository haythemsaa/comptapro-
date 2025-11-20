<?php

namespace App\Console\Commands\Belgium;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Services\Belgium\AutoTaxServiceBE;
use App\Services\Belgium\AutoAccountingServiceBE;

/**
 * Commande pour exécuter le workflow mensuel automatique Belgique
 *
 * Usage: php artisan belgium:monthly-workflow {company_id} {year} {month}
 */
class MonthlyWorkflowBECommand extends Command
{
    protected $signature = 'belgium:monthly-workflow
                            {company_id : ID de l\'entreprise}
                            {year : Année}
                            {month : Mois (1-12)}
                            {--force : Forcer même si déjà exécuté}';

    protected $description = 'Exécuter le workflow comptable mensuel automatique pour une entreprise belge';

    protected AutoTaxServiceBE $taxService;
    protected AutoAccountingServiceBE $accountingService;

    public function __construct(
        AutoTaxServiceBE $taxService,
        AutoAccountingServiceBE $accountingService
    ) {
        parent::__construct();
        $this->taxService = $taxService;
        $this->accountingService = $accountingService;
    }

    public function handle(): int
    {
        $companyId = $this->argument('company_id');
        $year = (int) $this->argument('year');
        $month = (int) $this->argument('month');

        $company = Company::find($companyId);

        if (!$company) {
            $this->error("❌ Entreprise #{$companyId} introuvable");
            return 1;
        }

        if ($company->country_code !== 'BE') {
            $this->error("❌ Cette entreprise n'est pas belge (pays: {$company->country_code})");
            return 1;
        }

        $this->info("🇧🇪 ComptaPro Belgium - Workflow Mensuel Automatique");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Entreprise: {$company->name}");
        $this->info("Période: {$month}/{$year}");
        $this->info("");

        $startTime = now();

        // Étape 1: Générer déclaration TVA
        $this->info("📋 Étape 1/4: Génération déclaration TVA...");
        $vatResult = $this->taxService->generateVATDeclaration($company, $year, $month);

        if ($vatResult['success']) {
            $this->info("   ✅ TVA générée: {$vatResult['declaration']->vat_to_pay}€");
            $this->info("   📄 Communication: {$vatResult['payment_reference']}");
        } else {
            $this->warn("   ⚠️  Erreur TVA: {$vatResult['error']}");
        }

        // Étape 2: Générer toutes les paies du mois
        $this->info("");
        $this->info("👥 Étape 2/4: Génération des paies...");

        $employees = $company->employees()->where('status', 'active')->get();
        $payrollsGenerated = 0;

        foreach ($employees as $employee) {
            try {
                $payrollData = \App\Models\Belgium\PayrollBE::calculate($employee, $year, $month);
                \App\Models\Belgium\PayrollBE::create($payrollData);
                $payrollsGenerated++;
                $this->info("   ✅ Paie générée: {$employee->full_name} - Net: {$payrollData['net_salary']}€");
            } catch (\Exception $e) {
                $this->warn("   ⚠️  Erreur paie {$employee->full_name}: {$e->getMessage()}");
            }
        }

        $this->info("   📊 Total: {$payrollsGenerated} fiches de paie créées");

        // Étape 3: Vérifier échéances
        $this->info("");
        $this->info("⏰ Étape 3/4: Vérification des échéances...");

        $deadlines = $this->taxService->checkDeadlines($company, $year, $month);

        if (count($deadlines) > 0) {
            $this->warn("   ⚠️  {count($deadlines)} échéances en attente:");
            foreach ($deadlines as $deadline) {
                $this->line("      - {$deadline['description']} (deadline: {$deadline['deadline']})");
            }
        } else {
            $this->info("   ✅ Aucune échéance en retard");
        }

        // Étape 4: Résumé
        $this->info("");
        $this->info("📊 Étape 4/4: Résumé du mois");

        $duration = $startTime->diffInSeconds(now());

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✅ Workflow terminé avec succès !");
        $this->info("");
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Durée totale', "{$duration} secondes"],
                ['Déclaration TVA', $vatResult['success'] ? '✅ Générée' : '❌ Erreur'],
                ['Fiches de paie', "{$payrollsGenerated} créées"],
                ['Échéances', count($deadlines) . ' en attente'],
            ]
        );

        $this->info("");
        $this->info("🎉 Le workflow mensuel est terminé !");
        $this->info("💡 Temps économisé vs manuel: ~4 heures");

        return 0;
    }
}
