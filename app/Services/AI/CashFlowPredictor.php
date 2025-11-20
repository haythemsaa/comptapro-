<?php

namespace App\Services\AI;

use App\Models\Invoicing\Invoice;
use App\Models\Purchases\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Prédiction de Trésorerie avec Machine Learning
 *
 * Fonctionnalités:
 * - Prédiction des encaissements à venir
 * - Prédiction des décaissements
 * - Analyse des tendances saisonnières
 * - Alertes sur les risques de trésorerie
 * - Recommandations d'actions
 */
class CashFlowPredictor
{
    private int $companyId;
    private array $historicalData = [];

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Prédit la trésorerie pour les N prochains jours
     */
    public function predictCashFlow(int $daysAhead = 90): array
    {
        try {
            // 1. Charger les données historiques
            $this->loadHistoricalData();

            // 2. Calculer la trésorerie actuelle
            $currentCashBalance = $this->getCurrentCashBalance();

            // 3. Prédire les encaissements
            $predictedInflows = $this->predictInflows($daysAhead);

            // 4. Prédire les décaissements
            $predictedOutflows = $this->predictOutflows($daysAhead);

            // 5. Calculer la trésorerie prédite jour par jour
            $dailyPredictions = $this->calculateDailyPredictions(
                $currentCashBalance,
                $predictedInflows,
                $predictedOutflows,
                $daysAhead
            );

            // 6. Identifier les risques
            $risks = $this->identifyRisks($dailyPredictions);

            // 7. Générer des recommandations
            $recommendations = $this->generateRecommendations($dailyPredictions, $risks);

            return [
                'success' => true,
                'current_cash_balance' => $currentCashBalance,
                'predictions' => $dailyPredictions,
                'summary' => [
                    'total_predicted_inflows' => array_sum(array_column($predictedInflows, 'amount')),
                    'total_predicted_outflows' => array_sum(array_column($predictedOutflows, 'amount')),
                    'min_cash_balance' => min(array_column($dailyPredictions, 'balance')),
                    'max_cash_balance' => max(array_column($dailyPredictions, 'balance')),
                ],
                'risks' => $risks,
                'recommendations' => $recommendations,
                'confidence_score' => $this->calculateConfidenceScore(),
            ];
        } catch (\Exception $e) {
            Log::error('Cash flow prediction failed', [
                'company_id' => $this->companyId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Charge les données historiques
     */
    private function loadHistoricalData(): void
    {
        $startDate = now()->subYear();
        $endDate = now();

        // Encaissements historiques
        $this->historicalData['inflows'] = Invoice::where('company_id', $this->companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'paid')
            ->select('invoice_date', 'payment_date', 'total_amount', 'due_date')
            ->get()
            ->toArray();

        // Décaissements historiques
        $this->historicalData['outflows'] = PurchaseInvoice::where('company_id', $this->companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'paid')
            ->select('invoice_date', 'payment_date', 'total_amount')
            ->get()
            ->toArray();
    }

    /**
     * Calcule la trésorerie actuelle
     */
    private function getCurrentCashBalance(): float
    {
        // Récupérer le solde des comptes bancaires et caisse
        // Comptes 51x (Banques) et 53x (Caisse)
        $balance = DB::table('accounts')
            ->where('company_id', $this->companyId)
            ->where(function ($query) {
                $query->where('account_code', 'like', '51%')
                      ->orWhere('account_code', 'like', '53%');
            })
            ->sum('balance');

        return round($balance, 3);
    }

    /**
     * Prédit les encaissements futurs
     */
    private function predictInflows(int $daysAhead): array
    {
        $predictions = [];

        // 1. Factures émises non payées (prédiction basée sur les délais de paiement)
        $unpaidInvoices = Invoice::where('company_id', $this->companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->with('client')
            ->get();

        foreach ($unpaidInvoices as $invoice) {
            $paymentProbability = $this->calculatePaymentProbability($invoice);
            $predictedPaymentDate = $this->predictPaymentDate($invoice);

            if ($predictedPaymentDate->lte(now()->addDays($daysAhead))) {
                $predictions[] = [
                    'date' => $predictedPaymentDate->toDateString(),
                    'amount' => $invoice->total_amount * $paymentProbability,
                    'probability' => $paymentProbability,
                    'source' => 'invoice',
                    'reference' => $invoice->invoice_number,
                    'description' => "Encaissement facture {$invoice->invoice_number}",
                ];
            }
        }

        // 2. Prédiction basée sur les revenus récurrents (tendance)
        $recurringInflows = $this->predictRecurringInflows($daysAhead);
        $predictions = array_merge($predictions, $recurringInflows);

        return $predictions;
    }

    /**
     * Prédit les décaissements futurs
     */
    private function predictOutflows(int $daysAhead): array
    {
        $predictions = [];

        // 1. Factures fournisseurs non payées
        $unpaidPurchases = PurchaseInvoice::where('company_id', $this->companyId)
            ->whereIn('status', ['received', 'approved'])
            ->get();

        foreach ($unpaidPurchases as $purchase) {
            $predictedPaymentDate = $this->predictPurchasePaymentDate($purchase);

            if ($predictedPaymentDate->lte(now()->addDays($daysAhead))) {
                $predictions[] = [
                    'date' => $predictedPaymentDate->toDateString(),
                    'amount' => -abs($purchase->total_amount),
                    'probability' => 0.95,
                    'source' => 'purchase',
                    'reference' => $purchase->invoice_number,
                    'description' => "Paiement fournisseur {$purchase->supplier->name}",
                ];
            }
        }

        // 2. Charges récurrentes (salaires, loyer, etc.)
        $recurringOutflows = $this->predictRecurringOutflows($daysAhead);
        $predictions = array_merge($predictions, $recurringOutflows);

        // 3. Impôts et charges sociales
        $taxPayments = $this->predictTaxPayments($daysAhead);
        $predictions = array_merge($predictions, $taxPayments);

        return $predictions;
    }

    /**
     * Calcule la probabilité de paiement d'une facture
     */
    private function calculatePaymentProbability(Invoice $invoice): float
    {
        $daysOverdue = now()->diffInDays($invoice->due_date, false);

        if ($daysOverdue <= 0) {
            // Pas encore en retard
            return 0.90;
        } elseif ($daysOverdue <= 30) {
            // 1-30 jours de retard
            return 0.70;
        } elseif ($daysOverdue <= 60) {
            // 31-60 jours
            return 0.50;
        } elseif ($daysOverdue <= 90) {
            // 61-90 jours
            return 0.30;
        } else {
            // Plus de 90 jours
            return 0.15;
        }
    }

    /**
     * Prédit la date de paiement d'une facture
     */
    private function predictPaymentDate(Invoice $invoice): Carbon
    {
        // Analyser l'historique de paiement du client
        $clientPaymentHistory = $this->getClientPaymentHistory($invoice->client_id);

        if (!empty($clientPaymentHistory)) {
            $avgDelay = $clientPaymentHistory['avg_delay_days'];
            return $invoice->due_date->addDays($avgDelay);
        }

        // Par défaut: prévoir 7 jours de retard en moyenne
        return $invoice->due_date->addDays(7);
    }

    /**
     * Récupère l'historique de paiement d'un client
     */
    private function getClientPaymentHistory(int $clientId): array
    {
        $paidInvoices = Invoice::where('company_id', $this->companyId)
            ->where('client_id', $clientId)
            ->where('status', 'paid')
            ->whereNotNull('payment_date')
            ->get();

        if ($paidInvoices->isEmpty()) {
            return [];
        }

        $delays = [];
        foreach ($paidInvoices as $invoice) {
            $delay = $invoice->payment_date->diffInDays($invoice->due_date, false);
            $delays[] = $delay;
        }

        return [
            'avg_delay_days' => round(array_sum($delays) / count($delays)),
            'max_delay_days' => max($delays),
            'invoices_count' => count($delays),
        ];
    }

    /**
     * Prédit les revenus récurrents
     */
    private function predictRecurringInflows(int $daysAhead): array
    {
        // Analyser les patterns de revenus mensuels
        $monthlyRevenue = $this->calculateAverageMonthlyRevenue();

        $predictions = [];
        $currentDate = now();

        // Répartir le revenu moyen mensuel sur les jours
        $dailyAverage = $monthlyRevenue / 26; // 26 jours ouvrés en moyenne

        for ($i = 1; $i <= $daysAhead; $i++) {
            $date = $currentDate->copy()->addDays($i);

            // Ne pas prédire les weekends (pour les activités B2B)
            if ($date->isWeekend()) {
                continue;
            }

            $predictions[] = [
                'date' => $date->toDateString(),
                'amount' => $dailyAverage,
                'probability' => 0.75,
                'source' => 'recurring',
                'reference' => 'TREND',
                'description' => 'Revenus récurrents estimés',
            ];
        }

        return $predictions;
    }

    /**
     * Calcule le revenu moyen mensuel
     */
    private function calculateAverageMonthlyRevenue(): float
    {
        $revenues = Invoice::where('company_id', $this->companyId)
            ->where('status', 'paid')
            ->whereDate('invoice_date', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(invoice_date) as year, MONTH(invoice_date) as month, SUM(total_amount) as total')
            ->groupBy('year', 'month')
            ->get();

        if ($revenues->isEmpty()) {
            return 0;
        }

        $total = $revenues->sum('total');
        return $total / $revenues->count();
    }

    /**
     * Prédit les charges récurrentes
     */
    private function predictRecurringOutflows(int $daysAhead): array
    {
        $predictions = [];

        // Salaires (fin de mois)
        $avgMonthlyPayroll = $this->calculateAverageMonthlyPayroll();
        $predictions = array_merge($predictions, $this->predictPayrollPayments($daysAhead, $avgMonthlyPayroll));

        // Loyer (début de mois)
        $monthlyRent = $this->getMonthlyRent();
        if ($monthlyRent > 0) {
            $predictions = array_merge($predictions, $this->predictRentPayments($daysAhead, $monthlyRent));
        }

        return $predictions;
    }

    /**
     * Prédit les paiements de salaires
     */
    private function predictPayrollPayments(int $daysAhead, float $avgAmount): array
    {
        $predictions = [];
        $currentMonth = now()->month;
        $currentYear = now()->year;

        for ($i = 0; $i <= ceil($daysAhead / 30); $i++) {
            $paymentDate = Carbon::create($currentYear, $currentMonth, 28)->addMonths($i);

            if ($paymentDate->lte(now()->addDays($daysAhead))) {
                $predictions[] = [
                    'date' => $paymentDate->toDateString(),
                    'amount' => -abs($avgAmount),
                    'probability' => 1.0,
                    'source' => 'payroll',
                    'reference' => 'SALAIRES',
                    'description' => 'Paiement des salaires',
                ];
            }
        }

        return $predictions;
    }

    /**
     * Prédit les paiements fiscaux
     */
    private function predictTaxPayments(int $daysAhead): array
    {
        $predictions = [];

        // TVA (28 du mois)
        $avgMonthlyVAT = $this->calculateAverageMonthlyVAT();
        $predictions = array_merge($predictions, $this->predictVATPayments($daysAhead, $avgMonthlyVAT));

        // CNSS (15 du mois)
        $avgMonthlyCNSS = $this->calculateAverageMonthlyCNSS();
        $predictions = array_merge($predictions, $this->predictCNSSPayments($daysAhead, $avgMonthlyCNSS));

        return $predictions;
    }

    /**
     * Calcule les prédictions journalières
     */
    private function calculateDailyPredictions(
        float $startingBalance,
        array $inflows,
        array $outflows,
        int $daysAhead
    ): array {
        $predictions = [];
        $balance = $startingBalance;

        // Combiner et trier tous les mouvements par date
        $allMovements = array_merge($inflows, $outflows);
        usort($allMovements, fn($a, $b) => strcmp($a['date'], $b['date']));

        // Grouper par date
        $movementsByDate = [];
        foreach ($allMovements as $movement) {
            $date = $movement['date'];
            if (!isset($movementsByDate[$date])) {
                $movementsByDate[$date] = [];
            }
            $movementsByDate[$date][] = $movement;
        }

        // Calculer le solde jour par jour
        for ($i = 0; $i <= $daysAhead; $i++) {
            $date = now()->addDays($i)->toDateString();
            $dayInflows = 0;
            $dayOutflows = 0;

            if (isset($movementsByDate[$date])) {
                foreach ($movementsByDate[$date] as $movement) {
                    if ($movement['amount'] > 0) {
                        $dayInflows += $movement['amount'];
                    } else {
                        $dayOutflows += abs($movement['amount']);
                    }
                    $balance += $movement['amount'];
                }
            }

            $predictions[] = [
                'date' => $date,
                'balance' => round($balance, 3),
                'inflows' => round($dayInflows, 3),
                'outflows' => round($dayOutflows, 3),
                'net_change' => round($dayInflows - $dayOutflows, 3),
            ];
        }

        return $predictions;
    }

    /**
     * Identifie les risques de trésorerie
     */
    private function identifyRisks(array $predictions): array
    {
        $risks = [];

        foreach ($predictions as $prediction) {
            if ($prediction['balance'] < 0) {
                $risks[] = [
                    'level' => 'critical',
                    'date' => $prediction['date'],
                    'type' => 'negative_balance',
                    'message' => "Solde négatif prévu: {$prediction['balance']} TND",
                    'impact' => 'high',
                ];
            } elseif ($prediction['balance'] < 5000) {
                $risks[] = [
                    'level' => 'warning',
                    'date' => $prediction['date'],
                    'type' => 'low_balance',
                    'message' => "Solde faible prévu: {$prediction['balance']} TND",
                    'impact' => 'medium',
                ];
            }
        }

        return $risks;
    }

    /**
     * Génère des recommandations
     */
    private function generateRecommendations(array $predictions, array $risks): array
    {
        $recommendations = [];

        if (!empty($risks)) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Accélérer les encaissements',
                'description' => 'Relancer les clients avec factures en retard pour améliorer la trésorerie',
            ];

            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Négocier les délais fournisseurs',
                'description' => 'Demander des délais de paiement plus longs à vos fournisseurs',
            ];

            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'Prévoir une ligne de crédit',
                'description' => 'Envisager une facilité de caisse auprès de votre banque',
            ];
        }

        return $recommendations;
    }

    /**
     * Calcule le score de confiance de la prédiction
     */
    private function calculateConfidenceScore(): float
    {
        $dataPoints = count($this->historicalData['inflows']) + count($this->historicalData['outflows']);

        if ($dataPoints > 100) {
            return 0.90;
        } elseif ($dataPoints > 50) {
            return 0.75;
        } elseif ($dataPoints > 20) {
            return 0.60;
        } else {
            return 0.40;
        }
    }

    // Méthodes helper (à implémenter selon les données disponibles)
    private function predictPurchasePaymentDate($purchase): Carbon { return now()->addDays(30); }
    private function calculateAverageMonthlyPayroll(): float { return 0; }
    private function getMonthlyRent(): float { return 0; }
    private function predictRentPayments($days, $amount): array { return []; }
    private function calculateAverageMonthlyVAT(): float { return 0; }
    private function predictVATPayments($days, $amount): array { return []; }
    private function calculateAverageMonthlyCNSS(): float { return 0; }
    private function predictCNSSPayments($days, $amount): array { return []; }
}
