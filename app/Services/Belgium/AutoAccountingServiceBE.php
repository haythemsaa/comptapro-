<?php

namespace App\Services\Belgium;

use App\Models\Company;
use App\Models\Belgium\BelgiumChartOfAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'automatisation comptable pour la Belgique
 * Utilise l'IA Claude pour:
 * - OCR de factures
 * - Extraction automatique des données
 * - Détermination comptes PCMN
 * - Génération écritures comptables
 */
class AutoAccountingServiceBE
{
    protected string $claudeApiKey;
    protected string $claudeModel = 'claude-sonnet-4-20250514';
    protected string $googleVisionApiKey;

    public function __construct()
    {
        $this->claudeApiKey = config('services.anthropic.api_key');
        $this->googleVisionApiKey = config('services.google_vision.api_key');
    }

    /**
     * Traiter automatiquement un document (facture, note de frais, etc.)
     */
    public function processDocument(
        Company $company,
        string $documentPath,
        string $documentType = 'invoice'
    ): array {
        try {
            // 1. OCR du document
            $ocrData = $this->performOCR($documentPath);

            // 2. Extraction des données avec IA
            $extractedData = $this->extractDataWithAI($ocrData, $documentType);

            // 3. Déterminer les comptes PCMN avec IA
            $accounts = $this->determineAccountsWithAI($extractedData, $documentType);

            // 4. Générer l'écriture comptable
            $journalEntry = $this->generateJournalEntry(
                $company,
                $extractedData,
                $accounts,
                $documentType
            );

            // 5. Valider automatiquement si confiance >= 90%
            $autoValidate = $extractedData['confidence'] >= 90;

            return [
                'success' => true,
                'extracted_data' => $extractedData,
                'accounts' => $accounts,
                'journal_entry' => $journalEntry,
                'auto_validated' => $autoValidate,
                'confidence' => $extractedData['confidence'],
            ];

        } catch (\Exception $e) {
            Log::error('Error processing document BE: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * OCR avec Google Cloud Vision API
     */
    protected function performOCR(string $documentPath): string
    {
        $imageContent = base64_encode(file_get_contents($documentPath));

        $response = Http::post("https://vision.googleapis.com/v1/images:annotate?key={$this->googleVisionApiKey}", [
            'requests' => [
                [
                    'image' => ['content' => $imageContent],
                    'features' => [
                        ['type' => 'TEXT_DETECTION'],
                        ['type' => 'DOCUMENT_TEXT_DETECTION']
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            return $result['responses'][0]['fullTextAnnotation']['text'] ?? '';
        }

        throw new \Exception('OCR failed: ' . $response->body());
    }

    /**
     * Extraire les données avec Claude AI
     */
    protected function extractDataWithAI(string $ocrText, string $documentType): array
    {
        $prompt = "Tu es un expert-comptable belge. Analyse ce texte OCR d'une {$documentType} belge et extrais les informations suivantes au format JSON:

{
    \"document_type\": \"invoice/expense/receipt\",
    \"document_number\": \"numéro de la facture\",
    \"date\": \"YYYY-MM-DD\",
    \"supplier_name\": \"nom du fournisseur\",
    \"supplier_vat\": \"numéro de TVA belge (BE...)\",
    \"customer_name\": \"nom du client\",
    \"customer_vat\": \"numéro de TVA client\",
    \"items\": [
        {
            \"description\": \"description\",
            \"quantity\": 1,
            \"unit_price\": 100.00,
            \"vat_rate\": 21,
            \"vat_amount\": 21.00,
            \"total_excl_vat\": 100.00,
            \"total_incl_vat\": 121.00
        }
    ],
    \"total_excl_vat\": 100.00,
    \"total_vat_21\": 0,
    \"total_vat_12\": 0,
    \"total_vat_6\": 0,
    \"total_vat_0\": 0,
    \"total_vat\": 21.00,
    \"total_incl_vat\": 121.00,
    \"payment_method\": \"bank_transfer/cash/card\",
    \"payment_reference\": \"communication structurée si présente\",
    \"bank_account\": \"IBAN\",
    \"confidence\": 95
}

Texte OCR:
{$ocrText}

Réponds UNIQUEMENT avec le JSON, sans texte supplémentaire.";

        $response = Http::withHeaders([
            'x-api-key' => $this->claudeApiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->claudeModel,
            'max_tokens' => 2000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $content = $result['content'][0]['text'];

            // Extraire le JSON
            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                return json_decode($matches[0], true);
            }
        }

        throw new \Exception('AI extraction failed: ' . $response->body());
    }

    /**
     * Déterminer les comptes PCMN avec Claude AI
     */
    protected function determineAccountsWithAI(array $extractedData, string $documentType): array
    {
        // Récupérer les comptes PCMN pertinents
        $relevantAccounts = BelgiumChartOfAccount::active()
            ->postable()
            ->get()
            ->map(fn($acc) => [
                'number' => $acc->account_number,
                'name_fr' => $acc->account_name,
                'name_nl' => $acc->account_name_nl,
                'type' => $acc->type,
                'class' => $acc->class,
            ])
            ->toArray();

        $accountsJson = json_encode($relevantAccounts, JSON_PRETTY_PRINT);

        $prompt = "Tu es un expert-comptable belge utilisant le PCMN (Plan Comptable Minimum Normalisé).

Pour cette opération:
Type: {$documentType}
Fournisseur: {$extractedData['supplier_name']}
Montant HT: {$extractedData['total_excl_vat']}€
TVA 21%: {$extractedData['total_vat_21']}€
TVA 12%: {$extractedData['total_vat_12']}€
TVA 6%: {$extractedData['total_vat_6']}€
Total TTC: {$extractedData['total_incl_vat']}€

Détermine les comptes PCMN appropriés selon la NATURE de la dépense (pas seulement le montant).

Exemples:
- Loyer bureau → 610 (Loyers et charges locatives)
- Électricité/Eau → 612 (Fournitures)
- Honoraires comptable → 613 (Rétributions tiers)
- Publicité → 614 (Annonces et publicité)
- Réparations → 611 (Entretien et réparations)
- Matériel informatique → 23 (Installations, machines)
- Fournitures bureau → 612 (Fournitures)
- Salaires → 620 (Rémunérations)
- ONSS → 621 (Cotisations patronales)
- Achats marchandises → 604 (Achats marchandises)

Comptes PCMN disponibles:
{$accountsJson}

Réponds UNIQUEMENT avec ce JSON:
{
    \"debit_account\": \"numéro compte PCMN débit\",
    \"debit_account_name\": \"nom du compte\",
    \"credit_account\": \"440\",
    \"credit_account_name\": \"Fournisseurs\",
    \"vat_account\": \"411\",
    \"vat_account_name\": \"TVA à récupérer\",
    \"confidence\": 95,
    \"reasoning\": \"explication du choix\"
}";

        $response = Http::withHeaders([
            'x-api-key' => $this->claudeApiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->claudeModel,
            'max_tokens' => 1000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $content = $result['content'][0]['text'];

            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                return json_decode($matches[0], true);
            }
        }

        throw new \Exception('AI account determination failed: ' . $response->body());
    }

    /**
     * Générer l'écriture comptable
     */
    protected function generateJournalEntry(
        Company $company,
        array $extractedData,
        array $accounts,
        string $documentType
    ): array {
        $lines = [];

        // Ligne de charge/achat
        $lines[] = [
            'account' => $accounts['debit_account'],
            'account_name' => $accounts['debit_account_name'],
            'debit' => $extractedData['total_excl_vat'],
            'credit' => 0,
            'description' => $extractedData['supplier_name'] . ' - ' . $extractedData['document_number']
        ];

        // Lignes TVA par taux
        if ($extractedData['total_vat_21'] > 0) {
            $lines[] = [
                'account' => '411',
                'account_name' => 'TVA à récupérer 21%',
                'debit' => $extractedData['total_vat_21'],
                'credit' => 0,
                'description' => 'TVA 21%'
            ];
        }

        if ($extractedData['total_vat_12'] > 0) {
            $lines[] = [
                'account' => '411',
                'account_name' => 'TVA à récupérer 12%',
                'debit' => $extractedData['total_vat_12'],
                'credit' => 0,
                'description' => 'TVA 12%'
            ];
        }

        if ($extractedData['total_vat_6'] > 0) {
            $lines[] = [
                'account' => '411',
                'account_name' => 'TVA à récupérer 6%',
                'debit' => $extractedData['total_vat_6'],
                'credit' => 0,
                'description' => 'TVA 6%'
            ];
        }

        // Ligne fournisseur
        $lines[] = [
            'account' => $accounts['credit_account'],
            'account_name' => $accounts['credit_account_name'],
            'debit' => 0,
            'credit' => $extractedData['total_incl_vat'],
            'description' => $extractedData['supplier_name']
        ];

        return [
            'company_id' => $company->id,
            'date' => $extractedData['date'],
            'reference' => $extractedData['document_number'],
            'description' => "{$extractedData['supplier_name']} - {$extractedData['document_number']}",
            'document_type' => $documentType,
            'lines' => $lines,
            'total_debit' => array_sum(array_column($lines, 'debit')),
            'total_credit' => array_sum(array_column($lines, 'credit')),
        ];
    }

    /**
     * Traiter une facture de vente
     */
    public function processSalesInvoice(Company $company, array $invoiceData): array
    {
        $lines = [];

        // Client
        $lines[] = [
            'account' => '400',
            'account_name' => 'Clients',
            'debit' => $invoiceData['total_incl_vat'],
            'credit' => 0,
            'description' => $invoiceData['customer_name']
        ];

        // Ventes par taux TVA
        if ($invoiceData['sales_21'] > 0) {
            $lines[] = [
                'account' => '700',
                'account_name' => 'Ventes marchandises',
                'debit' => 0,
                'credit' => $invoiceData['sales_21'],
                'description' => 'Ventes 21%'
            ];
            $lines[] = [
                'account' => '451',
                'account_name' => 'TVA à payer',
                'debit' => 0,
                'credit' => $invoiceData['vat_21'],
                'description' => 'TVA collectée 21%'
            ];
        }

        // ... autres taux

        return [
            'company_id' => $company->id,
            'date' => $invoiceData['date'],
            'reference' => $invoiceData['invoice_number'],
            'description' => "Facture {$invoiceData['customer_name']}",
            'lines' => $lines,
        ];
    }
}
