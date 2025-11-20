<?php

namespace App\Services\AI;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use App\Helpers\TunisiaHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Anthropic\Anthropic;

/**
 * Service d'automatisation de la comptabilité avec IA
 * Génère automatiquement les écritures comptables à partir des documents
 */
class AutoAccountingService
{
    private Anthropic $anthropic;
    private int $companyId;
    private IntelligentOCRService $ocrService;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
        $this->anthropic = new Anthropic(config('services.anthropic.key'));
        $this->ocrService = new IntelligentOCRService();
    }

    /**
     * Traite automatiquement tous les documents en attente
     */
    public function processAllPendingDocuments(): array
    {
        $results = [
            'invoices_processed' => 0,
            'expenses_processed' => 0,
            'journal_entries_created' => 0,
            'errors' => []
        ];

        try {
            // Traiter les factures de vente
            $pendingInvoices = Invoice::where('company_id', $this->companyId)
                ->where('is_accounted', false)
                ->get();

            foreach ($pendingInvoices as $invoice) {
                try {
                    $this->processInvoice($invoice);
                    $results['invoices_processed']++;
                    $results['journal_entries_created']++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Invoice {$invoice->id}: " . $e->getMessage();
                }
            }

            // Traiter les dépenses
            $pendingExpenses = Expense::where('company_id', $this->companyId)
                ->where('is_accounted', false)
                ->get();

            foreach ($pendingExpenses as $expense) {
                try {
                    $this->processExpense($expense);
                    $results['expenses_processed']++;
                    $results['journal_entries_created']++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Expense {$expense->id}: " . $e->getMessage();
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('AutoAccounting Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Traite automatiquement une facture et génère l'écriture comptable
     */
    public function processInvoice(Invoice $invoice): JournalEntry
    {
        DB::beginTransaction();
        try {
            // Analyser la facture avec l'IA
            $analysis = $this->analyzeInvoiceWithAI($invoice);

            // Créer l'écriture comptable
            $journalEntry = JournalEntry::create([
                'company_id' => $this->companyId,
                'journal_type' => 'sales',
                'date' => $invoice->invoice_date,
                'reference' => "VTE-{$invoice->invoice_number}",
                'description' => "Vente - {$invoice->customer_name}",
                'is_validated' => false,
                'created_by_ai' => true,
                'ai_confidence' => $analysis['confidence']
            ]);

            // Ligne débit: Client (411)
            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getAccountByCode('411')->id,
                'label' => "Client - {$invoice->customer_name}",
                'debit' => $invoice->total_ttc,
                'credit' => 0,
            ]);

            // Ligne crédit: Vente (707)
            $saleAccount = $this->getAccountByCode($analysis['sale_account']);
            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $saleAccount->id,
                'label' => $analysis['sale_description'],
                'debit' => 0,
                'credit' => $invoice->total_ht,
            ]);

            // Ligne crédit: TVA collectée (4451)
            if ($invoice->vat_amount > 0) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $this->getAccountByCode('4451')->id,
                    'label' => "TVA collectée {$invoice->vat_rate}%",
                    'debit' => 0,
                    'credit' => $invoice->vat_amount,
                ]);
            }

            // Marquer la facture comme comptabilisée
            $invoice->update([
                'is_accounted' => true,
                'journal_entry_id' => $journalEntry->id
            ]);

            DB::commit();

            Log::info("Invoice {$invoice->id} automatically accounted by AI");

            return $journalEntry;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Traite automatiquement une dépense et génère l'écriture comptable
     */
    public function processExpense(Expense $expense): JournalEntry
    {
        DB::beginTransaction();
        try {
            // Analyser la dépense avec l'IA
            $analysis = $this->analyzeExpenseWithAI($expense);

            // Créer l'écriture comptable
            $journalEntry = JournalEntry::create([
                'company_id' => $this->companyId,
                'journal_type' => 'purchases',
                'date' => $expense->expense_date,
                'reference' => "ACH-{$expense->reference}",
                'description' => "Achat - {$expense->supplier_name}",
                'is_validated' => false,
                'created_by_ai' => true,
                'ai_confidence' => $analysis['confidence']
            ]);

            // Ligne débit: Charge (6xx)
            $expenseAccount = $this->getAccountByCode($analysis['expense_account']);
            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $expenseAccount->id,
                'label' => $analysis['expense_description'],
                'debit' => $expense->amount_ht,
                'credit' => 0,
            ]);

            // Ligne débit: TVA déductible (4456)
            if ($expense->vat_amount > 0) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $this->getAccountByCode('4456')->id,
                    'label' => "TVA déductible {$expense->vat_rate}%",
                    'debit' => $expense->vat_amount,
                    'credit' => 0,
                ]);
            }

            // Ligne crédit: Fournisseur (401)
            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getAccountByCode('401')->id,
                'label' => "Fournisseur - {$expense->supplier_name}",
                'debit' => 0,
                'credit' => $expense->amount_ttc,
            ]);

            // Marquer la dépense comme comptabilisée
            $expense->update([
                'is_accounted' => true,
                'journal_entry_id' => $journalEntry->id
            ]);

            DB::commit();

            Log::info("Expense {$expense->id} automatically accounted by AI");

            return $journalEntry;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Analyse une facture avec l'IA pour déterminer les comptes appropriés
     */
    private function analyzeInvoiceWithAI(Invoice $invoice): array
    {
        $prompt = "Tu es un expert-comptable tunisien. Analyse cette facture et détermine:
1. Le compte de vente approprié (classe 7)
2. Une description précise pour l'écriture

Facture:
- Client: {$invoice->customer_name}
- Description: {$invoice->description}
- Montant HT: {$invoice->total_ht} TND
- TVA: {$invoice->vat_rate}%

Réponds en JSON:
{
  \"sale_account\": \"code du compte (ex: 707)\",
  \"sale_description\": \"description détaillée\",
  \"confidence\": 0.95
}";

        try {
            $response = $this->anthropic->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            $content = $response->content[0]->text;
            $result = json_decode($content, true);

            return $result ?: [
                'sale_account' => '707',
                'sale_description' => "Vente de marchandises",
                'confidence' => 0.7
            ];
        } catch (\Exception $e) {
            // Fallback avec des règles par défaut
            return [
                'sale_account' => '707',
                'sale_description' => "Vente de marchandises",
                'confidence' => 0.5
            ];
        }
    }

    /**
     * Analyse une dépense avec l'IA pour déterminer les comptes appropriés
     */
    private function analyzeExpenseWithAI(Expense $expense): array
    {
        $prompt = "Tu es un expert-comptable tunisien. Analyse cette dépense et détermine:
1. Le compte de charge approprié (classe 6)
2. Une description précise pour l'écriture

Dépense:
- Fournisseur: {$expense->supplier_name}
- Description: {$expense->description}
- Catégorie: {$expense->category}
- Montant HT: {$expense->amount_ht} TND
- TVA: {$expense->vat_rate}%

Réponds en JSON:
{
  \"expense_account\": \"code du compte (ex: 607, 613, 622, etc.)\",
  \"expense_description\": \"description détaillée\",
  \"confidence\": 0.95
}";

        try {
            $response = $this->anthropic->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            $content = $response->content[0]->text;
            $result = json_decode($content, true);

            return $result ?: [
                'expense_account' => '607',
                'expense_description' => "Achat de marchandises",
                'confidence' => 0.7
            ];
        } catch (\Exception $e) {
            // Fallback avec des règles par défaut
            return $this->getDefaultExpenseAccount($expense);
        }
    }

    /**
     * Détermine le compte de charge par défaut selon la catégorie
     */
    private function getDefaultExpenseAccount(Expense $expense): array
    {
        $mapping = [
            'raw_materials' => ['607', 'Achat de matières premières'],
            'supplies' => ['606', 'Achat de fournitures'],
            'services' => ['613', 'Location'],
            'maintenance' => ['615', 'Entretien et réparations'],
            'insurance' => ['616', 'Primes d\'assurance'],
            'utilities' => ['622', 'Rémunérations d\'intermédiaires'],
            'marketing' => ['623', 'Publicité et relations publiques'],
            'transport' => ['624', 'Transports de biens et de personnel'],
            'travel' => ['625', 'Déplacements, missions et réceptions'],
            'professional_fees' => ['622', 'Honoraires'],
            'bank_charges' => ['627', 'Services bancaires'],
            'taxes' => ['635', 'Autres impôts et taxes'],
            'salaries' => ['641', 'Rémunérations du personnel'],
            'depreciation' => ['681', 'Dotations aux amortissements'],
        ];

        $default = $mapping[$expense->category] ?? ['607', 'Achat de marchandises'];

        return [
            'expense_account' => $default[0],
            'expense_description' => $default[1],
            'confidence' => 0.6
        ];
    }

    /**
     * Scanne et importe automatiquement des documents uploadés
     */
    public function scanAndImportDocument(string $filePath, string $documentType = 'invoice'): array
    {
        try {
            // Extraire les données avec OCR
            $ocrResult = $this->ocrService->extractInvoiceData($filePath);

            if (!$ocrResult['success']) {
                throw new \Exception('OCR extraction failed');
            }

            $data = $ocrResult['data'];

            // Créer l'entité appropriée
            if ($documentType === 'invoice') {
                $invoice = Invoice::create([
                    'company_id' => $this->companyId,
                    'invoice_number' => $data['invoice_number'],
                    'invoice_date' => $data['invoice_date'],
                    'customer_name' => $data['supplier_name'], // Client
                    'customer_tax_id' => $data['supplier_tax_id'],
                    'total_ht' => $data['total_ht'],
                    'vat_rate' => $data['vat_rate'],
                    'vat_amount' => $data['vat_amount'],
                    'total_ttc' => $data['total_ttc'],
                    'description' => 'Importée automatiquement par IA',
                    'is_accounted' => false,
                    'imported_by_ai' => true,
                    'ai_confidence' => $ocrResult['confidence']
                ]);

                // Traiter immédiatement l'écriture
                $journalEntry = $this->processInvoice($invoice);

                return [
                    'success' => true,
                    'invoice' => $invoice,
                    'journal_entry' => $journalEntry,
                    'confidence' => $ocrResult['confidence']
                ];
            } else {
                $expense = Expense::create([
                    'company_id' => $this->companyId,
                    'reference' => $data['invoice_number'],
                    'expense_date' => $data['invoice_date'],
                    'supplier_name' => $data['supplier_name'],
                    'supplier_tax_id' => $data['supplier_tax_id'],
                    'amount_ht' => $data['total_ht'],
                    'vat_rate' => $data['vat_rate'],
                    'vat_amount' => $data['vat_amount'],
                    'amount_ttc' => $data['total_ttc'],
                    'description' => 'Importée automatiquement par IA',
                    'category' => 'other',
                    'is_accounted' => false,
                    'imported_by_ai' => true,
                    'ai_confidence' => $ocrResult['confidence']
                ]);

                // Traiter immédiatement l'écriture
                $journalEntry = $this->processExpense($expense);

                return [
                    'success' => true,
                    'expense' => $expense,
                    'journal_entry' => $journalEntry,
                    'confidence' => $ocrResult['confidence']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Document scan error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valide automatiquement les écritures avec un niveau de confiance élevé
     */
    public function autoValidateHighConfidenceEntries(): int
    {
        $validated = 0;

        $entries = JournalEntry::where('company_id', $this->companyId)
            ->where('created_by_ai', true)
            ->where('is_validated', false)
            ->where('ai_confidence', '>=', 0.9)
            ->get();

        foreach ($entries as $entry) {
            $entry->update(['is_validated' => true]);
            $validated++;
        }

        return $validated;
    }

    /**
     * Récupère un compte par son code
     */
    private function getAccountByCode(string $code): ChartOfAccount
    {
        $account = ChartOfAccount::where('company_id', $this->companyId)
            ->where('code', $code)
            ->first();

        if (!$account) {
            throw new \Exception("Account {$code} not found");
        }

        return $account;
    }
}
