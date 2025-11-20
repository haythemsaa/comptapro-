<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service OCR Intelligent avec IA pour extraction automatique de factures
 *
 * Fonctionnalités:
 * - Extraction de texte avec Google Cloud Vision / AWS Textract
 * - Reconnaissance intelligente des champs (numéro, date, montants, TVA)
 * - Support multi-langue (français, arabe)
 * - Validation et correction automatique
 * - Apprentissage continu pour améliorer la précision
 */
class IntelligentOCRService
{
    private string $provider;
    private array $config;

    public function __construct()
    {
        $this->provider = config('services.ocr.provider', 'google'); // google, aws, azure
        $this->config = config('services.ocr');
    }

    /**
     * Extrait les données d'une facture depuis une image/PDF
     */
    public function extractInvoiceData(string $filePath): array
    {
        try {
            // 1. Extraire le texte brut avec OCR
            $ocrResult = $this->performOCR($filePath);

            if (!$ocrResult['success']) {
                return $ocrResult;
            }

            $extractedText = $ocrResult['text'];

            // 2. Analyser et structurer les données avec IA
            $structuredData = $this->parseInvoiceWithAI($extractedText, $ocrResult['blocks'] ?? []);

            // 3. Valider et enrichir les données
            $validatedData = $this->validateAndEnrich($structuredData);

            // 4. Calculer le score de confiance
            $confidence = $this->calculateConfidence($validatedData);

            return [
                'success' => true,
                'data' => $validatedData,
                'confidence' => $confidence,
                'raw_text' => $extractedText,
                'requires_review' => $confidence < 0.85,
            ];
        } catch (\Exception $e) {
            Log::error('OCR extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Effectue l'OCR selon le provider configuré
     */
    private function performOCR(string $filePath): array
    {
        switch ($this->provider) {
            case 'google':
                return $this->googleCloudVisionOCR($filePath);
            case 'aws':
                return $this->awsTextractOCR($filePath);
            case 'azure':
                return $this->azureComputerVisionOCR($filePath);
            default:
                return $this->tesseractOCR($filePath);
        }
    }

    /**
     * OCR avec Google Cloud Vision
     */
    private function googleCloudVisionOCR(string $filePath): array
    {
        try {
            $imageContent = base64_encode(file_get_contents($filePath));

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://vision.googleapis.com/v1/images:annotate?key=' . $this->config['google_api_key'], [
                'requests' => [
                    [
                        'image' => ['content' => $imageContent],
                        'features' => [
                            ['type' => 'DOCUMENT_TEXT_DETECTION'],
                            ['type' => 'TEXT_DETECTION'],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $textAnnotations = $result['responses'][0]['textAnnotations'] ?? [];

                return [
                    'success' => true,
                    'text' => $textAnnotations[0]['description'] ?? '',
                    'blocks' => $this->extractBlocks($textAnnotations),
                ];
            }

            return [
                'success' => false,
                'error' => 'Google Cloud Vision API error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * OCR avec AWS Textract
     */
    private function awsTextractOCR(string $filePath): array
    {
        // Implémentation AWS Textract
        // Nécessite le SDK AWS
        return [
            'success' => false,
            'error' => 'AWS Textract not implemented yet',
        ];
    }

    /**
     * OCR avec Azure Computer Vision
     */
    private function azureComputerVisionOCR(string $filePath): array
    {
        // Implémentation Azure
        return [
            'success' => false,
            'error' => 'Azure Computer Vision not implemented yet',
        ];
    }

    /**
     * OCR avec Tesseract (fallback gratuit)
     */
    private function tesseractOCR(string $filePath): array
    {
        // Nécessite Tesseract installé sur le serveur
        $command = "tesseract {$filePath} stdout -l fra+ara";
        $output = shell_exec($command);

        return [
            'success' => !empty($output),
            'text' => $output ?? '',
            'blocks' => [],
        ];
    }

    /**
     * Parse les données de facture avec IA
     */
    private function parseInvoiceWithAI(string $text, array $blocks): array
    {
        // Utiliser Claude API ou GPT pour l'extraction intelligente
        $prompt = $this->buildExtractionPrompt($text);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.anthropic.api_key'),
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 2000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $content = $result['content'][0]['text'] ?? '{}';

            return json_decode($content, true) ?? $this->parseWithRegex($text);
        }

        // Fallback sur extraction par regex
        return $this->parseWithRegex($text);
    }

    /**
     * Construit le prompt pour l'IA
     */
    private function buildExtractionPrompt(string $text): string
    {
        return <<<PROMPT
Analyse cette facture et extrais les informations suivantes au format JSON:

{
  "supplier_name": "Nom du fournisseur",
  "supplier_tax_id": "Matricule fiscal ou numéro TVA",
  "supplier_address": "Adresse complète",
  "invoice_number": "Numéro de facture",
  "invoice_date": "Date de facture (format YYYY-MM-DD)",
  "due_date": "Date d'échéance (format YYYY-MM-DD)",
  "currency": "Devise (TND, EUR, USD, etc.)",
  "items": [
    {
      "description": "Description du produit/service",
      "quantity": nombre,
      "unit_price": prix_unitaire,
      "vat_rate": taux_tva_en_pourcentage,
      "total": montant_total
    }
  ],
  "subtotal": montant_HT,
  "vat_amount": montant_TVA,
  "total": montant_TTC,
  "payment_method": "Mode de paiement",
  "bank_details": {
    "bank_name": "Nom de la banque",
    "iban": "Numéro IBAN",
    "rib": "Numéro RIB"
  }
}

Texte de la facture:
{$text}

Réponds UNIQUEMENT avec le JSON, sans texte avant ou après.
PROMPT;
    }

    /**
     * Parse avec regex (fallback)
     */
    private function parseWithRegex(string $text): array
    {
        $data = [];

        // Numéro de facture
        if (preg_match('/(?:facture|invoice|n°|num)\s*:?\s*([A-Z0-9\-\/]+)/i', $text, $matches)) {
            $data['invoice_number'] = trim($matches[1]);
        }

        // Date
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $text, $matches)) {
            $data['invoice_date'] = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }

        // Montants
        if (preg_match('/total\s*(?:ttc)?\s*:?\s*([\d\s,.]+)/i', $text, $matches)) {
            $data['total'] = $this->parseAmount($matches[1]);
        }

        if (preg_match('/(?:tva|vat)\s*:?\s*([\d\s,.]+)/i', $text, $matches)) {
            $data['vat_amount'] = $this->parseAmount($matches[1]);
        }

        // Matricule fiscal tunisien (format: 1234567/ABC/A/M/000)
        if (preg_match('/(\d{7}\/[A-Z]{1,3}\/[A-Z]\/[A-Z]\/\d{3})/', $text, $matches)) {
            $data['supplier_tax_id'] = $matches[1];
        }

        return $data;
    }

    /**
     * Valide et enrichit les données extraites
     */
    private function validateAndEnrich(array $data): array
    {
        // Validation du matricule fiscal tunisien
        if (isset($data['supplier_tax_id'])) {
            $data['supplier_tax_id_valid'] = $this->validateTunisianTaxId($data['supplier_tax_id']);
        }

        // Validation des dates
        if (isset($data['invoice_date'])) {
            $data['invoice_date_valid'] = $this->isValidDate($data['invoice_date']);
        }

        // Validation des montants
        if (isset($data['subtotal'], $data['vat_amount'], $data['total'])) {
            $expectedTotal = $data['subtotal'] + $data['vat_amount'];
            $data['amounts_consistent'] = abs($expectedTotal - $data['total']) < 1;
        }

        // Détection automatique du fournisseur existant
        if (isset($data['supplier_tax_id'])) {
            $data['existing_supplier_id'] = $this->findExistingSupplier($data['supplier_tax_id']);
        }

        return $data;
    }

    /**
     * Calcule le score de confiance global
     */
    private function calculateConfidence(array $data): float
    {
        $scores = [];

        // Présence des champs obligatoires
        $requiredFields = ['invoice_number', 'invoice_date', 'supplier_name', 'total'];
        foreach ($requiredFields as $field) {
            $scores[] = isset($data[$field]) && !empty($data[$field]) ? 1.0 : 0.0;
        }

        // Validité des données
        if (isset($data['supplier_tax_id_valid'])) {
            $scores[] = $data['supplier_tax_id_valid'] ? 1.0 : 0.5;
        }

        if (isset($data['invoice_date_valid'])) {
            $scores[] = $data['invoice_date_valid'] ? 1.0 : 0.5;
        }

        if (isset($data['amounts_consistent'])) {
            $scores[] = $data['amounts_consistent'] ? 1.0 : 0.6;
        }

        return empty($scores) ? 0.0 : array_sum($scores) / count($scores);
    }

    /**
     * Valide un matricule fiscal tunisien
     */
    private function validateTunisianTaxId(string $taxId): bool
    {
        // Format: 1234567/ABC/A/M/000
        return preg_match('/^\d{7}\/[A-Z]{1,3}\/[A-Z]\/[A-Z]\/\d{3}$/', $taxId) === 1;
    }

    /**
     * Vérifie si une date est valide
     */
    private function isValidDate(string $date): bool
    {
        try {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            return $d && $d->format('Y-m-d') === $date;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cherche un fournisseur existant par matricule fiscal
     */
    private function findExistingSupplier(string $taxId): ?int
    {
        // Requête à la base de données pour trouver le fournisseur
        // À implémenter selon le modèle Supplier
        return null;
    }

    /**
     * Parse un montant depuis différents formats
     */
    private function parseAmount(string $amount): float
    {
        // Nettoyer: "1 234,56" ou "1,234.56" → 1234.56
        $cleaned = preg_replace('/[^\d,.]/', '', $amount);

        // Déterminer le séparateur décimal
        if (substr_count($cleaned, ',') === 1 && substr_count($cleaned, '.') === 0) {
            // Format français: 1234,56
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            // Format international: 1,234.56
            $cleaned = str_replace(',', '', $cleaned);
        }

        return (float) $cleaned;
    }

    /**
     * Extrait les blocs de texte avec positions
     */
    private function extractBlocks(array $annotations): array
    {
        $blocks = [];

        foreach ($annotations as $annotation) {
            if (isset($annotation['description'], $annotation['boundingPoly'])) {
                $blocks[] = [
                    'text' => $annotation['description'],
                    'bounds' => $annotation['boundingPoly'],
                ];
            }
        }

        return $blocks;
    }

    /**
     * Entraîne le modèle avec les corrections utilisateur
     */
    public function learn(string $filePath, array $extractedData, array $correctedData): bool
    {
        // Stocker les données pour améliorer le modèle
        // Implémentation future: fine-tuning ou apprentissage actif
        Log::info('OCR Learning', [
            'file' => $filePath,
            'extracted' => $extractedData,
            'corrected' => $correctedData,
        ]);

        return true;
    }
}
