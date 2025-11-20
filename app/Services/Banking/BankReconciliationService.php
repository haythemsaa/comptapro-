<?php

namespace App\Services\Banking;

use App\Models\Accounting\JournalEntry;
use App\Models\Banking\BankTransaction;
use App\Models\Banking\BankStatement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de Rapprochement Bancaire Automatique avec IA
 *
 * Fonctionnalités:
 * - Import automatique des relevés bancaires
 * - Matching intelligent des transactions
 * - Suggestions de rapprochement par IA
 * - Gestion des écritures non rapprochées
 * - Détection d'anomalies
 */
class BankReconciliationService
{
    private int $companyId;
    private array $matchedTransactions = [];
    private array $unmatchedBankTransactions = [];
    private array $unmatchedAccountingEntries = [];

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Effectue le rapprochement bancaire automatique
     */
    public function reconcile(
        int $bankAccountId,
        string $startDate,
        string $endDate,
        ?array $bankTransactions = null
    ): array {
        try {
            // 1. Charger ou importer les transactions bancaires
            if ($bankTransactions === null) {
                $bankTransactions = $this->loadBankTransactions($bankAccountId, $startDate, $endDate);
            }

            // 2. Charger les écritures comptables
            $accountingEntries = $this->loadAccountingEntries($bankAccountId, $startDate, $endDate);

            // 3. Matching automatique
            $this->performAutoMatching($bankTransactions, $accountingEntries);

            // 4. Matching intelligent avec IA pour les transactions restantes
            $this->performAIMatching();

            // 5. Calculer les soldes
            $balances = $this->calculateBalances($bankAccountId, $endDate);

            // 6. Générer le rapport
            return [
                'success' => true,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
                'matched_count' => count($this->matchedTransactions),
                'unmatched_bank_count' => count($this->unmatchedBankTransactions),
                'unmatched_accounting_count' => count($this->unmatchedAccountingEntries),
                'matched_transactions' => $this->matchedTransactions,
                'unmatched_bank_transactions' => $this->unmatchedBankTransactions,
                'unmatched_accounting_entries' => $this->unmatchedAccountingEntries,
                'balances' => $balances,
                'suggestions' => $this->generateSuggestions(),
            ];
        } catch (\Exception $e) {
            Log::error('Bank reconciliation failed', [
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
     * Charge les transactions bancaires
     */
    private function loadBankTransactions(int $bankAccountId, string $startDate, string $endDate): array
    {
        return BankTransaction::where('company_id', $this->companyId)
            ->where('bank_account_id', $bankAccountId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->whereNull('reconciled_at')
            ->orderBy('transaction_date')
            ->get()
            ->toArray();
    }

    /**
     * Charge les écritures comptables
     */
    private function loadAccountingEntries(int $bankAccountId, string $startDate, string $endDate): array
    {
        // Récupérer le compte comptable associé au compte bancaire
        $accountCode = $this->getBankAccountCode($bankAccountId);

        return JournalEntry::where('company_id', $this->companyId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->whereHas('lines', function ($query) use ($accountCode) {
                $query->where('account_code', $accountCode);
            })
            ->with('lines')
            ->orderBy('entry_date')
            ->get()
            ->map(function ($entry) use ($accountCode) {
                $line = $entry->lines->firstWhere('account_code', $accountCode);
                return [
                    'id' => $entry->id,
                    'entry_number' => $entry->entry_number,
                    'date' => $entry->entry_date,
                    'description' => $entry->description,
                    'amount' => ($line->debit ?? 0) - ($line->credit ?? 0),
                    'debit' => $line->debit ?? 0,
                    'credit' => $line->credit ?? 0,
                    'reconciled' => false,
                ];
            })
            ->toArray();
    }

    /**
     * Matching automatique exact
     */
    private function performAutoMatching(array &$bankTransactions, array &$accountingEntries): void
    {
        foreach ($bankTransactions as $bankIndex => $bankTx) {
            foreach ($accountingEntries as $accountingIndex => $accountingEntry) {
                if ($accountingEntry['reconciled']) {
                    continue;
                }

                // Critères de matching
                $amountMatch = $this->amountsMatch($bankTx['amount'], $accountingEntry['amount']);
                $dateMatch = $this->datesMatch($bankTx['transaction_date'], $accountingEntry['date']);
                $descriptionMatch = $this->descriptionsMatch(
                    $bankTx['description'] ?? '',
                    $accountingEntry['description'] ?? ''
                );

                // Match parfait: montant + date proche
                if ($amountMatch && $dateMatch) {
                    $this->createMatch($bankTx, $accountingEntry, 'exact', 1.0);
                    $accountingEntries[$accountingIndex]['reconciled'] = true;
                    unset($bankTransactions[$bankIndex]);
                    continue 2;
                }
            }
        }

        // Réindexer les arrays
        $bankTransactions = array_values($bankTransactions);

        // Stocker les non rapprochés
        $this->unmatchedBankTransactions = $bankTransactions;
        $this->unmatchedAccountingEntries = array_filter(
            $accountingEntries,
            fn($e) => !$e['reconciled']
        );
    }

    /**
     * Matching intelligent avec IA
     */
    private function performAIMatching(): void
    {
        if (empty($this->unmatchedBankTransactions) || empty($this->unmatchedAccountingEntries)) {
            return;
        }

        foreach ($this->unmatchedBankTransactions as $bankIndex => $bankTx) {
            $bestMatch = null;
            $bestScore = 0;

            foreach ($this->unmatchedAccountingEntries as $accountingIndex => $accountingEntry) {
                $score = $this->calculateMatchingScore($bankTx, $accountingEntry);

                if ($score > $bestScore && $score >= 0.7) {
                    $bestScore = $score;
                    $bestMatch = ['index' => $accountingIndex, 'entry' => $accountingEntry];
                }
            }

            // Si score suffisant, suggérer le match
            if ($bestMatch && $bestScore >= 0.7) {
                $this->createMatch($bankTx, $bestMatch['entry'], 'ai_suggested', $bestScore);

                // Si score très élevé (>= 0.9), auto-rapprocher
                if ($bestScore >= 0.9) {
                    unset($this->unmatchedBankTransactions[$bankIndex]);
                    unset($this->unmatchedAccountingEntries[$bestMatch['index']]);
                }
            }
        }

        // Réindexer
        $this->unmatchedBankTransactions = array_values($this->unmatchedBankTransactions);
        $this->unmatchedAccountingEntries = array_values($this->unmatchedAccountingEntries);
    }

    /**
     * Calcule le score de matching entre deux transactions
     */
    private function calculateMatchingScore(array $bankTx, array $accountingEntry): float
    {
        $scores = [];

        // 1. Score de montant (50%)
        $amountScore = $this->calculateAmountScore($bankTx['amount'], $accountingEntry['amount']);
        $scores[] = $amountScore * 0.5;

        // 2. Score de date (30%)
        $dateScore = $this->calculateDateScore($bankTx['transaction_date'], $accountingEntry['date']);
        $scores[] = $dateScore * 0.3;

        // 3. Score de description (20%)
        $descriptionScore = $this->calculateDescriptionScore(
            $bankTx['description'] ?? '',
            $accountingEntry['description'] ?? ''
        );
        $scores[] = $descriptionScore * 0.2;

        return array_sum($scores);
    }

    /**
     * Score de correspondance des montants
     */
    private function calculateAmountScore(float $amount1, float $amount2): float
    {
        if (abs($amount1 - $amount2) < 0.01) {
            return 1.0;
        }

        $difference = abs($amount1 - $amount2);
        $average = (abs($amount1) + abs($amount2)) / 2;

        if ($average == 0) {
            return 0.0;
        }

        $percentDiff = ($difference / $average) * 100;

        if ($percentDiff <= 1) {
            return 0.9;
        } elseif ($percentDiff <= 5) {
            return 0.7;
        } elseif ($percentDiff <= 10) {
            return 0.5;
        }

        return 0.0;
    }

    /**
     * Score de correspondance des dates
     */
    private function calculateDateScore(string $date1, string $date2): float
    {
        $d1 = \Carbon\Carbon::parse($date1);
        $d2 = \Carbon\Carbon::parse($date2);

        $daysDiff = abs($d1->diffInDays($d2));

        if ($daysDiff === 0) {
            return 1.0;
        } elseif ($daysDiff <= 2) {
            return 0.9;
        } elseif ($daysDiff <= 5) {
            return 0.7;
        } elseif ($daysDiff <= 10) {
            return 0.5;
        }

        return 0.3;
    }

    /**
     * Score de correspondance des descriptions
     */
    private function calculateDescriptionScore(string $desc1, string $desc2): float
    {
        $desc1 = strtolower(trim($desc1));
        $desc2 = strtolower(trim($desc2));

        if (empty($desc1) || empty($desc2)) {
            return 0.5;
        }

        if ($desc1 === $desc2) {
            return 1.0;
        }

        // Similarité de chaîne
        similar_text($desc1, $desc2, $percent);

        return $percent / 100;
    }

    /**
     * Vérifie si les montants correspondent
     */
    private function amountsMatch(float $amount1, float $amount2, float $tolerance = 0.01): bool
    {
        return abs($amount1 - $amount2) < $tolerance;
    }

    /**
     * Vérifie si les dates correspondent (avec tolérance de 5 jours)
     */
    private function datesMatch(string $date1, string $date2, int $toleranceDays = 5): bool
    {
        $d1 = \Carbon\Carbon::parse($date1);
        $d2 = \Carbon\Carbon::parse($date2);

        return abs($d1->diffInDays($d2)) <= $toleranceDays;
    }

    /**
     * Vérifie si les descriptions correspondent
     */
    private function descriptionsMatch(string $desc1, string $desc2, float $threshold = 0.7): bool
    {
        $desc1 = strtolower(trim($desc1));
        $desc2 = strtolower(trim($desc2));

        if (empty($desc1) || empty($desc2)) {
            return false;
        }

        similar_text($desc1, $desc2, $percent);

        return ($percent / 100) >= $threshold;
    }

    /**
     * Crée un match entre transaction bancaire et écriture comptable
     */
    private function createMatch(array $bankTx, array $accountingEntry, string $type, float $confidence): void
    {
        $this->matchedTransactions[] = [
            'bank_transaction' => $bankTx,
            'accounting_entry' => $accountingEntry,
            'match_type' => $type,
            'confidence' => $confidence,
            'matched_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Calcule les soldes
     */
    private function calculateBalances(int $bankAccountId, string $endDate): array
    {
        // Solde comptable
        $accountingBalance = $this->getAccountingBalance($bankAccountId, $endDate);

        // Solde bancaire
        $bankBalance = $this->getBankBalance($bankAccountId, $endDate);

        // Différence
        $difference = $bankBalance - $accountingBalance;

        return [
            'accounting_balance' => round($accountingBalance, 3),
            'bank_balance' => round($bankBalance, 3),
            'difference' => round($difference, 3),
            'reconciled' => abs($difference) < 0.01,
        ];
    }

    /**
     * Récupère le solde comptable
     */
    private function getAccountingBalance(int $bankAccountId, string $date): float
    {
        $accountCode = $this->getBankAccountCode($bankAccountId);

        $result = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entry_lines.account_code', $accountCode)
            ->where('journal_entries.entry_date', '<=', $date)
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        return ($result->total_debit ?? 0) - ($result->total_credit ?? 0);
    }

    /**
     * Récupère le solde bancaire
     */
    private function getBankBalance(int $bankAccountId, string $date): float
    {
        $statement = BankStatement::where('bank_account_id', $bankAccountId)
            ->where('statement_date', '<=', $date)
            ->orderBy('statement_date', 'desc')
            ->first();

        return $statement?->ending_balance ?? 0;
    }

    /**
     * Récupère le code comptable du compte bancaire
     */
    private function getBankAccountCode(int $bankAccountId): string
    {
        // Assumons que les comptes bancaires sont en 512x
        // À adapter selon votre structure
        return '5121';
    }

    /**
     * Génère des suggestions d'action
     */
    private function generateSuggestions(): array
    {
        $suggestions = [];

        if (count($this->unmatchedBankTransactions) > 0) {
            $suggestions[] = [
                'type' => 'unmatched_bank',
                'priority' => 'high',
                'message' => count($this->unmatchedBankTransactions) . ' transaction(s) bancaire(s) non rapprochée(s)',
                'action' => 'Vérifier et créer les écritures comptables manquantes',
            ];
        }

        if (count($this->unmatchedAccountingEntries) > 0) {
            $suggestions[] = [
                'type' => 'unmatched_accounting',
                'priority' => 'medium',
                'message' => count($this->unmatchedAccountingEntries) . ' écriture(s) comptable(s) non rapprochée(s)',
                'action' => 'Vérifier si ces opérations apparaissent sur le relevé bancaire',
            ];
        }

        return $suggestions;
    }

    /**
     * Import d'un relevé bancaire depuis un fichier
     */
    public function importBankStatement(int $bankAccountId, string $filePath, string $format = 'csv'): array
    {
        try {
            $transactions = match ($format) {
                'csv' => $this->importCSV($filePath),
                'ofx' => $this->importOFX($filePath),
                'qif' => $this->importQIF($filePath),
                default => throw new \Exception("Format non supporté: {$format}"),
            };

            // Enregistrer les transactions
            $imported = 0;
            foreach ($transactions as $transaction) {
                BankTransaction::create([
                    'company_id' => $this->companyId,
                    'bank_account_id' => $bankAccountId,
                    'transaction_date' => $transaction['date'],
                    'description' => $transaction['description'],
                    'amount' => $transaction['amount'],
                    'balance' => $transaction['balance'] ?? null,
                    'reference' => $transaction['reference'] ?? null,
                ]);
                $imported++;
            }

            return [
                'success' => true,
                'imported_count' => $imported,
                'transactions' => $transactions,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Import depuis CSV
     */
    private function importCSV(string $filePath): array
    {
        $transactions = [];
        $file = fopen($filePath, 'r');

        // Skip header
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $transactions[] = [
                'date' => $row[0],
                'description' => $row[1],
                'amount' => (float) $row[2],
                'balance' => isset($row[3]) ? (float) $row[3] : null,
            ];
        }

        fclose($file);

        return $transactions;
    }

    /**
     * Import depuis OFX (à implémenter)
     */
    private function importOFX(string $filePath): array
    {
        // À implémenter avec une bibliothèque OFX
        return [];
    }

    /**
     * Import depuis QIF (à implémenter)
     */
    private function importQIF(string $filePath): array
    {
        // À implémenter
        return [];
    }
}
