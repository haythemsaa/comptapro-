<?php

namespace App\Services\Tax;

use App\Models\Invoicing\Invoice;
use App\Models\Purchases\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Calculateur de TVA conforme à la législation tunisienne
 *
 * Taux de TVA en Tunisie:
 * - 19% : Taux normal
 * - 13% : Taux réduit (certains produits alimentaires, services)
 * - 7% : Taux super-réduit (équipements agricoles, etc.)
 * - 0% : Exportations
 */
class TunisianVATCalculator
{
    // Taux de TVA tunisiens
    public const VAT_RATE_STANDARD = 19;
    public const VAT_RATE_REDUCED = 13;
    public const VAT_RATE_SUPER_REDUCED = 7;
    public const VAT_RATE_EXPORT = 0;

    /**
     * Calcule la déclaration de TVA pour une période donnée
     */
    public function calculateVATDeclaration(
        int $companyId,
        Carbon $startDate,
        Carbon $endDate,
        bool $isMonthly = true
    ): array {
        // TVA collectée (ventes)
        $vatCollected = $this->calculateVATCollected($companyId, $startDate, $endDate);

        // TVA déductible (achats)
        $vatDeductible = $this->calculateVATDeductible($companyId, $startDate, $endDate);

        // Calcul du résultat
        $totalCollected = $vatCollected['total_vat_collected'];
        $totalDeductible = $vatDeductible['total_vat_deductible'];
        $vatResult = $totalCollected - $totalDeductible;

        return [
            // Période
            'period_type' => $isMonthly ? 'monthly' : 'quarterly',
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'month' => $isMonthly ? $startDate->month : null,
            'quarter' => !$isMonthly ? $startDate->quarter : null,
            'year' => $startDate->year,

            // TVA collectée
            'sales_19_ht' => $vatCollected['sales_19_ht'],
            'sales_19_vat' => $vatCollected['sales_19_vat'],
            'sales_13_ht' => $vatCollected['sales_13_ht'],
            'sales_13_vat' => $vatCollected['sales_13_vat'],
            'sales_7_ht' => $vatCollected['sales_7_ht'],
            'sales_7_vat' => $vatCollected['sales_7_vat'],
            'sales_export_ht' => $vatCollected['sales_export_ht'],
            'sales_exempt_ht' => $vatCollected['sales_exempt_ht'],
            'total_sales_ht' => $vatCollected['total_sales_ht'],
            'total_vat_collected' => $totalCollected,

            // TVA déductible
            'purchases_vat_immobilisations' => $vatDeductible['vat_immobilisations'],
            'purchases_vat_goods' => $vatDeductible['vat_goods'],
            'purchases_vat_services' => $vatDeductible['vat_services'],
            'purchases_vat_import' => $vatDeductible['vat_import'],
            'total_vat_deductible' => $totalDeductible,

            // Résultat
            'vat_to_pay' => $vatResult > 0 ? round($vatResult, 3) : 0,
            'vat_credit' => $vatResult < 0 ? round(abs($vatResult), 3) : 0,
            'due_date' => $this->calculateDueDate($endDate),

            // Détails
            'invoices_count' => $vatCollected['invoices_count'],
            'purchase_invoices_count' => $vatDeductible['purchase_invoices_count'],
        ];
    }

    /**
     * Calcule la TVA collectée (ventes)
     */
    private function calculateVATCollected(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        $invoices = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('status', ['sent', 'paid', 'overdue'])
            ->with('items')
            ->get();

        $sales19Ht = 0;
        $sales19Vat = 0;
        $sales13Ht = 0;
        $sales13Vat = 0;
        $sales7Ht = 0;
        $sales7Vat = 0;
        $salesExportHt = 0;
        $salesExemptHt = 0;

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $vatRate = $item->vat_rate;
                $subtotal = $item->subtotal; // HT
                $vatAmount = $item->vat_amount;

                if ($vatRate == 19) {
                    $sales19Ht += $subtotal;
                    $sales19Vat += $vatAmount;
                } elseif ($vatRate == 13) {
                    $sales13Ht += $subtotal;
                    $sales13Vat += $vatAmount;
                } elseif ($vatRate == 7) {
                    $sales7Ht += $subtotal;
                    $sales7Vat += $vatAmount;
                } elseif ($vatRate == 0) {
                    // Vérifier si c'est une exportation ou exonération
                    if ($this->isExport($invoice)) {
                        $salesExportHt += $subtotal;
                    } else {
                        $salesExemptHt += $subtotal;
                    }
                }
            }
        }

        return [
            'sales_19_ht' => round($sales19Ht, 3),
            'sales_19_vat' => round($sales19Vat, 3),
            'sales_13_ht' => round($sales13Ht, 3),
            'sales_13_vat' => round($sales13Vat, 3),
            'sales_7_ht' => round($sales7Ht, 3),
            'sales_7_vat' => round($sales7Vat, 3),
            'sales_export_ht' => round($salesExportHt, 3),
            'sales_exempt_ht' => round($salesExemptHt, 3),
            'total_sales_ht' => round(
                $sales19Ht + $sales13Ht + $sales7Ht + $salesExportHt + $salesExemptHt,
                3
            ),
            'total_vat_collected' => round(
                $sales19Vat + $sales13Vat + $sales7Vat,
                3
            ),
            'invoices_count' => $invoices->count(),
        ];
    }

    /**
     * Calcule la TVA déductible (achats)
     */
    private function calculateVATDeductible(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        $purchaseInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('status', ['received', 'paid'])
            ->with('items')
            ->get();

        $vatImmobilisations = 0;
        $vatGoods = 0;
        $vatServices = 0;
        $vatImport = 0;

        foreach ($purchaseInvoices as $purchase) {
            $purchaseVat = 0;

            foreach ($purchase->items as $item) {
                $purchaseVat += $item->vat_amount ?? 0;
            }

            // Catégoriser la TVA déductible
            $category = $this->categorizePurchase($purchase);

            switch ($category) {
                case 'immobilisation':
                    $vatImmobilisations += $purchaseVat;
                    break;
                case 'import':
                    $vatImport += $purchaseVat;
                    break;
                case 'service':
                    $vatServices += $purchaseVat;
                    break;
                default:
                    $vatGoods += $purchaseVat;
            }
        }

        return [
            'vat_immobilisations' => round($vatImmobilisations, 3),
            'vat_goods' => round($vatGoods, 3),
            'vat_services' => round($vatServices, 3),
            'vat_import' => round($vatImport, 3),
            'total_vat_deductible' => round(
                $vatImmobilisations + $vatGoods + $vatServices + $vatImport,
                3
            ),
            'purchase_invoices_count' => $purchaseInvoices->count(),
        ];
    }

    /**
     * Vérifie si une facture est une exportation
     */
    private function isExport(Invoice $invoice): bool
    {
        // Vérifier si le client est à l'étranger
        $client = $invoice->client;
        return $client && $client->country && $client->country !== 'TN';
    }

    /**
     * Catégorise un achat pour la TVA déductible
     */
    private function categorizePurchase(PurchaseInvoice $purchase): string
    {
        // Logique de catégorisation basée sur le type de produit/service
        $description = strtolower($purchase->description ?? '');

        // Immobilisations
        if (
            str_contains($description, 'immobilisation') ||
            str_contains($description, 'équipement') ||
            str_contains($description, 'matériel') ||
            str_contains($description, 'véhicule') ||
            str_contains($description, 'ordinateur')
        ) {
            return 'immobilisation';
        }

        // Import
        if (
            str_contains($description, 'import') ||
            str_contains($description, 'douane')
        ) {
            return 'import';
        }

        // Services
        if (
            str_contains($description, 'service') ||
            str_contains($description, 'prestation') ||
            str_contains($description, 'honoraire') ||
            str_contains($description, 'conseil') ||
            str_contains($description, 'maintenance')
        ) {
            return 'service';
        }

        return 'goods';
    }

    /**
     * Calcule la date limite de déclaration (28 du mois suivant)
     */
    private function calculateDueDate(Carbon $periodEnd): string
    {
        return $periodEnd->copy()->addMonth()->day(28)->toDateString();
    }

    /**
     * Génère le numéro de déclaration
     */
    public function generateDeclarationNumber(int $companyId, int $year, int $month): string
    {
        return sprintf('VAT-%d-%04d%02d', $companyId, $year, $month);
    }

    /**
     * Vérifie si l'entreprise doit déclarer mensuellement ou trimestriellement
     */
    public function shouldDeclareMonthly(int $companyId, int $year): bool
    {
        // Seuil: CA > 100 000 TND/an = déclaration mensuelle
        // Sinon = déclaration trimestrielle

        $totalSales = Invoice::where('company_id', $companyId)
            ->whereYear('invoice_date', $year - 1) // Année précédente
            ->whereIn('status', ['sent', 'paid', 'overdue'])
            ->sum('total_amount');

        return $totalSales > 100000;
    }

    /**
     * Calcule les intérêts de retard en cas de paiement tardif
     */
    public function calculateLatePenalty(float $vatAmount, Carbon $dueDate, Carbon $paymentDate): float
    {
        if ($paymentDate->lte($dueDate)) {
            return 0;
        }

        $daysLate = $dueDate->diffInDays($paymentDate);
        $monthsLate = ceil($daysLate / 30);

        // Pénalité de retard: 0.5% par mois + 1.25% d'intérêt de retard par mois
        $penaltyRate = 0.005 + 0.0125; // 1.75% par mois
        $penalty = $vatAmount * $penaltyRate * $monthsLate;

        return round($penalty, 3);
    }

    /**
     * Exporte la déclaration au format TEIF (format électronique tunisien)
     */
    public function exportToTEIF(array $declaration): string
    {
        // Format TEIF pour télédéclaration
        // Structure simplifiée - à compléter selon les spécifications officielles

        $lines = [];

        // En-tête
        $lines[] = "TEIF|TVA|{$declaration['year']}{$declaration['month']}";

        // Ventes
        $lines[] = "VENTE|19|{$declaration['sales_19_ht']}|{$declaration['sales_19_vat']}";
        $lines[] = "VENTE|13|{$declaration['sales_13_ht']}|{$declaration['sales_13_vat']}";
        $lines[] = "VENTE|7|{$declaration['sales_7_ht']}|{$declaration['sales_7_vat']}";
        $lines[] = "EXPORT|0|{$declaration['sales_export_ht']}|0";

        // Achats
        $lines[] = "ACHAT|IMM|{$declaration['purchases_vat_immobilisations']}";
        $lines[] = "ACHAT|BIE|{$declaration['purchases_vat_goods']}";
        $lines[] = "ACHAT|SRV|{$declaration['purchases_vat_services']}";
        $lines[] = "ACHAT|IMP|{$declaration['purchases_vat_import']}";

        // Résultat
        $lines[] = "RESULTAT|{$declaration['vat_to_pay']}|{$declaration['vat_credit']}";

        return implode("\n", $lines);
    }

    /**
     * Valide une déclaration de TVA
     */
    public function validateDeclaration(array $declaration): array
    {
        $errors = [];

        // Vérifications de cohérence
        $calculatedTotal = $declaration['sales_19_ht'] +
                          $declaration['sales_13_ht'] +
                          $declaration['sales_7_ht'] +
                          $declaration['sales_export_ht'] +
                          $declaration['sales_exempt_ht'];

        if (abs($calculatedTotal - $declaration['total_sales_ht']) > 0.01) {
            $errors[] = "Le total des ventes HT ne correspond pas à la somme des détails";
        }

        $calculatedVAT = $declaration['sales_19_vat'] +
                        $declaration['sales_13_vat'] +
                        $declaration['sales_7_vat'];

        if (abs($calculatedVAT - $declaration['total_vat_collected']) > 0.01) {
            $errors[] = "Le total de la TVA collectée ne correspond pas à la somme des détails";
        }

        // Vérifier les taux de TVA
        if ($declaration['sales_19_ht'] > 0) {
            $expectedVAT = round($declaration['sales_19_ht'] * 0.19, 3);
            if (abs($expectedVAT - $declaration['sales_19_vat']) > 0.5) {
                $errors[] = "La TVA à 19% semble incorrecte (attendu: {$expectedVAT}, obtenu: {$declaration['sales_19_vat']})";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
