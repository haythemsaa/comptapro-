<?php

namespace App\Services\AI;

use App\Models\Accounting\JournalEntry;
use App\Models\Invoicing\Invoice;
use App\Models\Purchases\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de Détection d'Anomalies Comptables avec IA
 *
 * Détecte automatiquement:
 * - Erreurs de saisie
 * - Transactions suspectes
 * - Incohérences comptables
 * - Fraudes potentielles
 * - Non-conformités réglementaires
 * - Doublons
 */
class AnomalyDetectionService
{
    private int $companyId;
    private array $anomalies = [];

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Analyse complète et détection d'anomalies
     */
    public function detectAnomalies(array $options = []): array
    {
        $this->anomalies = [];

        // 1. Vérifier les écritures comptables
        $this->checkJournalEntries($options['period'] ?? null);

        // 2. Vérifier les factures
        $this->checkInvoices($options['period'] ?? null);

        // 3. Vérifier les factures d'achat
        $this->checkPurchaseInvoices($options['period'] ?? null);

        // 4. Vérifier la cohérence TVA
        $this->checkVATConsistency();

        // 5. Détecter les doublons
        $this->detectDuplicates();

        // 6. Vérifier les montants suspects
        $this->detectSuspiciousAmounts();

        // 7. Vérifier la conformité des numérotations
        $this->checkNumberingConsistency();

        // 8. Analyser les patterns inhabituels
        $this->analyzeUnusualPatterns();

        // Trier par sévérité
        usort($this->anomalies, fn($a, $b) => $this->getSeverityWeight($b['severity']) - $this->getSeverityWeight($a['severity']));

        return [
            'success' => true,
            'anomalies_count' => count($this->anomalies),
            'anomalies' => $this->anomalies,
            'summary' => $this->generateSummary(),
            'recommendations' => $this->generateRecommendations(),
        ];
    }

    /**
     * Vérifie les écritures comptables
     */
    private function checkJournalEntries(?array $period): void
    {
        $query = JournalEntry::where('company_id', $this->companyId);

        if ($period) {
            $query->whereBetween('entry_date', [$period['start'], $period['end']]);
        }

        $entries = $query->with('lines')->get();

        foreach ($entries as $entry) {
            // Vérifier l'équilibre débit/crédit
            $totalDebit = $entry->lines->sum('debit');
            $totalCredit = $entry->lines->sum('credit');

            if (abs($totalDebit - $totalCredit) > 0.01) {
                $this->addAnomaly([
                    'type' => 'unbalanced_entry',
                    'severity' => 'high',
                    'entity_type' => 'journal_entry',
                    'entity_id' => $entry->id,
                    'reference' => $entry->entry_number,
                    'date' => $entry->entry_date,
                    'description' => 'Écriture non équilibrée',
                    'details' => "Débit: {$totalDebit} TND, Crédit: {$totalCredit} TND, Différence: " . abs($totalDebit - $totalCredit) . " TND",
                    'impact' => 'Les états financiers sont incorrects',
                    'suggested_fix' => 'Corriger l\'écriture pour équilibrer débit et crédit',
                ]);
            }

            // Vérifier les montants ronds suspects (possibles erreurs de saisie)
            foreach ($entry->lines as $line) {
                if ($line->debit > 0 && $this->isRoundNumber($line->debit)) {
                    $this->addAnomaly([
                        'type' => 'round_number',
                        'severity' => 'low',
                        'entity_type' => 'journal_entry',
                        'entity_id' => $entry->id,
                        'reference' => $entry->entry_number,
                        'date' => $entry->entry_date,
                        'description' => 'Montant rond suspect',
                        'details' => "Débit de {$line->debit} TND sur le compte {$line->account_code}",
                        'impact' => 'Possible erreur de saisie ou estimation',
                        'suggested_fix' => 'Vérifier le justificatif pour confirmer le montant exact',
                    ]);
                }
            }

            // Vérifier les dates incohérentes
            if ($entry->entry_date->isFuture()) {
                $this->addAnomaly([
                    'type' => 'future_date',
                    'severity' => 'medium',
                    'entity_type' => 'journal_entry',
                    'entity_id' => $entry->id,
                    'reference' => $entry->entry_number,
                    'date' => $entry->entry_date,
                    'description' => 'Date d\'écriture dans le futur',
                    'details' => "Date: {$entry->entry_date->format('Y-m-d')}",
                    'impact' => 'Incohérence temporelle',
                    'suggested_fix' => 'Corriger la date de l\'écriture',
                ]);
            }
        }
    }

    /**
     * Vérifie les factures clients
     */
    private function checkInvoices(?array $period): void
    {
        $query = Invoice::where('company_id', $this->companyId);

        if ($period) {
            $query->whereBetween('invoice_date', [$period['start'], $period['end']]);
        }

        $invoices = $query->with('items')->get();

        foreach ($invoices as $invoice) {
            // Vérifier la cohérence des totaux
            $calculatedSubtotal = $invoice->items->sum('subtotal');
            $calculatedVAT = $invoice->items->sum('vat_amount');
            $calculatedTotal = $calculatedSubtotal + $calculatedVAT;

            if (abs($invoice->subtotal - $calculatedSubtotal) > 0.5) {
                $this->addAnomaly([
                    'type' => 'incorrect_subtotal',
                    'severity' => 'high',
                    'entity_type' => 'invoice',
                    'entity_id' => $invoice->id,
                    'reference' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date,
                    'description' => 'Sous-total incorrect',
                    'details' => "Sous-total facture: {$invoice->subtotal} TND, Calculé: {$calculatedSubtotal} TND",
                    'impact' => 'Montant facturé incorrect',
                    'suggested_fix' => 'Recalculer et corriger le sous-total',
                ]);
            }

            // Vérifier les remises excessives
            if (isset($invoice->discount) && $invoice->discount > $invoice->subtotal * 0.5) {
                $discountPercent = ($invoice->discount / $invoice->subtotal) * 100;
                $this->addAnomaly([
                    'type' => 'excessive_discount',
                    'severity' => 'medium',
                    'entity_type' => 'invoice',
                    'entity_id' => $invoice->id,
                    'reference' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date,
                    'description' => 'Remise excessive',
                    'details' => "Remise de {$discountPercent}% ({$invoice->discount} TND)",
                    'impact' => 'Perte de revenu importante',
                    'suggested_fix' => 'Vérifier l\'approbation de cette remise',
                ]);
            }

            // Vérifier les factures avec TVA 0% pour clients locaux
            if ($invoice->client && $invoice->client->country === 'TN') {
                $hasZeroVAT = $invoice->items->contains(fn($item) => $item->vat_rate == 0);

                if ($hasZeroVAT && $invoice->total_amount > 1000) {
                    $this->addAnomaly([
                        'type' => 'missing_vat',
                        'severity' => 'high',
                        'entity_type' => 'invoice',
                        'entity_id' => $invoice->id,
                        'reference' => $invoice->invoice_number,
                        'date' => $invoice->invoice_date,
                        'description' => 'TVA manquante pour client tunisien',
                        'details' => "Client local avec TVA 0% pour {$invoice->total_amount} TND",
                        'impact' => 'Non-conformité fiscale',
                        'suggested_fix' => 'Appliquer la TVA appropriée (19%, 13% ou 7%)',
                    ]);
                }
            }
        }
    }

    /**
     * Vérifie les factures fournisseurs
     */
    private function checkPurchaseInvoices(?array $period): void
    {
        $query = PurchaseInvoice::where('company_id', $this->companyId);

        if ($period) {
            $query->whereBetween('invoice_date', [$period['start'], $period['end']]);
        }

        $purchases = $query->get();

        foreach ($purchases as $purchase) {
            // Vérifier les doublons de numéro de facture du même fournisseur
            $duplicates = PurchaseInvoice::where('company_id', $this->companyId)
                ->where('supplier_id', $purchase->supplier_id)
                ->where('invoice_number', $purchase->invoice_number)
                ->where('id', '!=', $purchase->id)
                ->count();

            if ($duplicates > 0) {
                $this->addAnomaly([
                    'type' => 'duplicate_invoice',
                    'severity' => 'critical',
                    'entity_type' => 'purchase_invoice',
                    'entity_id' => $purchase->id,
                    'reference' => $purchase->invoice_number,
                    'date' => $purchase->invoice_date,
                    'description' => 'Facture fournisseur en double',
                    'details' => "Numéro {$purchase->invoice_number} déjà existant pour ce fournisseur",
                    'impact' => 'Risque de double paiement',
                    'suggested_fix' => 'Vérifier et supprimer le doublon',
                ]);
            }
        }
    }

    /**
     * Vérifie la cohérence de la TVA
     */
    private function checkVATConsistency(): void
    {
        // Vérifier que les taux de TVA sont conformes aux taux tunisiens
        $validRates = [0, 7, 13, 19];

        $invalidVATInvoices = Invoice::where('company_id', $this->companyId)
            ->whereHas('items', function ($query) use ($validRates) {
                $query->whereNotIn('vat_rate', $validRates);
            })
            ->with('items')
            ->get();

        foreach ($invalidVATInvoices as $invoice) {
            $invalidItems = $invoice->items->whereNotIn('vat_rate', $validRates);

            foreach ($invalidItems as $item) {
                $this->addAnomaly([
                    'type' => 'invalid_vat_rate',
                    'severity' => 'high',
                    'entity_type' => 'invoice',
                    'entity_id' => $invoice->id,
                    'reference' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date,
                    'description' => 'Taux de TVA invalide',
                    'details' => "Taux de {$item->vat_rate}% utilisé (valides: 0%, 7%, 13%, 19%)",
                    'impact' => 'Non-conformité fiscale',
                    'suggested_fix' => 'Corriger le taux de TVA',
                ]);
            }
        }
    }

    /**
     * Détecte les doublons
     */
    private function detectDuplicates(): void
    {
        // Détecter les factures clients en double (même client, même montant, même période)
        $duplicateInvoices = DB::table('invoices')
            ->select('client_id', 'total_amount', 'invoice_date', DB::raw('COUNT(*) as count'))
            ->where('company_id', $this->companyId)
            ->groupBy('client_id', 'total_amount', 'invoice_date')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateInvoices as $duplicate) {
            $this->addAnomaly([
                'type' => 'potential_duplicate',
                'severity' => 'medium',
                'entity_type' => 'invoice',
                'entity_id' => null,
                'reference' => 'Multiple',
                'date' => $duplicate->invoice_date,
                'description' => 'Factures potentiellement en double',
                'details' => "{$duplicate->count} factures pour le même client, montant {$duplicate->total_amount} TND, date {$duplicate->invoice_date}",
                'impact' => 'Possible erreur de facturation',
                'suggested_fix' => 'Vérifier et supprimer les doublons si nécessaire',
            ]);
        }
    }

    /**
     * Détecte les montants suspects
     */
    private function detectSuspiciousAmounts(): void
    {
        // Analyser les montants statistiquement anormaux (outliers)
        $invoiceAmounts = Invoice::where('company_id', $this->companyId)
            ->pluck('total_amount')
            ->toArray();

        if (count($invoiceAmounts) > 10) {
            $stats = $this->calculateStatistics($invoiceAmounts);
            $threshold = $stats['mean'] + (3 * $stats['std_dev']); // 3 écarts-types

            $suspiciousInvoices = Invoice::where('company_id', $this->companyId)
                ->where('total_amount', '>', $threshold)
                ->get();

            foreach ($suspiciousInvoices as $invoice) {
                $this->addAnomaly([
                    'type' => 'unusual_amount',
                    'severity' => 'low',
                    'entity_type' => 'invoice',
                    'entity_id' => $invoice->id,
                    'reference' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date,
                    'description' => 'Montant inhabituellement élevé',
                    'details' => "Montant: {$invoice->total_amount} TND (seuil: {$threshold} TND)",
                    'impact' => 'À vérifier',
                    'suggested_fix' => 'Confirmer que le montant est correct',
                ]);
            }
        }
    }

    /**
     * Vérifie la cohérence de la numérotation
     */
    private function checkNumberingConsistency(): void
    {
        // Vérifier les trous dans la numérotation des factures
        $invoices = Invoice::where('company_id', $this->companyId)
            ->orderBy('invoice_number')
            ->pluck('invoice_number')
            ->toArray();

        $gaps = $this->findNumberingGaps($invoices);

        foreach ($gaps as $gap) {
            $this->addAnomaly([
                'type' => 'numbering_gap',
                'severity' => 'medium',
                'entity_type' => 'invoice',
                'entity_id' => null,
                'reference' => "Entre {$gap['before']} et {$gap['after']}",
                'date' => now(),
                'description' => 'Trou dans la numérotation des factures',
                'details' => "Numéros manquants entre {$gap['before']} et {$gap['after']}",
                'impact' => 'Non-conformité avec les obligations de numérotation continue',
                'suggested_fix' => 'Vérifier si des factures ont été supprimées ou mal numérotées',
            ]);
        }
    }

    /**
     * Analyse les patterns inhabituels
     */
    private function analyzeUnusualPatterns(): void
    {
        // Détecter les activités suspectes en dehors des heures normales
        $lateNightEntries = JournalEntry::where('company_id', $this->companyId)
            ->whereRaw('HOUR(created_at) BETWEEN 23 AND 5')
            ->count();

        if ($lateNightEntries > 10) {
            $this->addAnomaly([
                'type' => 'unusual_timing',
                'severity' => 'low',
                'entity_type' => 'journal_entry',
                'entity_id' => null,
                'reference' => 'Multiple',
                'date' => now(),
                'description' => 'Saisies comptables en dehors des heures normales',
                'details' => "{$lateNightEntries} écritures créées entre 23h et 5h",
                'impact' => 'Pattern inhabituel à surveiller',
                'suggested_fix' => 'Vérifier la légitimité de ces saisies',
            ]);
        }
    }

    /**
     * Ajoute une anomalie à la liste
     */
    private function addAnomaly(array $anomaly): void
    {
        $this->anomalies[] = array_merge($anomaly, [
            'detected_at' => now()->toIso8601String(),
            'status' => 'new',
        ]);
    }

    /**
     * Génère un résumé des anomalies
     */
    private function generateSummary(): array
    {
        $byType = [];
        $bySeverity = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];

        foreach ($this->anomalies as $anomaly) {
            $type = $anomaly['type'];
            $severity = $anomaly['severity'];

            $byType[$type] = ($byType[$type] ?? 0) + 1;
            $bySeverity[$severity]++;
        }

        return [
            'total' => count($this->anomalies),
            'by_type' => $byType,
            'by_severity' => $bySeverity,
            'critical_count' => $bySeverity['critical'],
            'requires_immediate_attention' => $bySeverity['critical'] + $bySeverity['high'],
        ];
    }

    /**
     * Génère des recommandations
     */
    private function generateRecommendations(): array
    {
        $recommendations = [];

        $critical = array_filter($this->anomalies, fn($a) => $a['severity'] === 'critical');

        if (!empty($critical)) {
            $recommendations[] = "🚨 URGENT: " . count($critical) . " anomalie(s) critique(s) nécessitent une action immédiate";
        }

        return $recommendations;
    }

    /**
     * Poids de sévérité pour tri
     */
    private function getSeverityWeight(string $severity): int
    {
        return match ($severity) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 0,
        };
    }

    /**
     * Vérifie si un nombre est "rond" (suspect)
     */
    private function isRoundNumber(float $number): bool
    {
        return $number >= 1000 && $number % 100 === 0;
    }

    /**
     * Calcule les statistiques d'un array
     */
    private function calculateStatistics(array $values): array
    {
        $count = count($values);
        $mean = array_sum($values) / $count;

        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;
        $stdDev = sqrt($variance);

        return [
            'mean' => $mean,
            'std_dev' => $stdDev,
            'min' => min($values),
            'max' => max($values),
        ];
    }

    /**
     * Trouve les trous dans la numérotation
     */
    private function findNumberingGaps(array $numbers): array
    {
        $gaps = [];
        // Simplification: à implémenter selon le format des numéros
        return $gaps;
    }
}
