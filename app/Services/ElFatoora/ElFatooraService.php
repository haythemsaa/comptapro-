<?php

namespace App\Services\ElFatoora;

use App\Models\Invoicing\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Service d'intégration avec El Fatoora (Système de facturation électronique tunisien)
 *
 * El Fatoora est le système obligatoire de facturation électronique en Tunisie
 * géré par le Ministère des Finances.
 *
 * Fonctionnalités:
 * - Signature électronique des factures
 * - Génération de QR codes conformes
 * - Télétransmission au système fiscal
 * - Validation et archivage
 */
class ElFatooraService
{
    private string $apiUrl;
    private string $apiKey;
    private string $certificatePath;
    private string $privateKeyPath;

    public function __construct()
    {
        $this->apiUrl = config('services.elfatoora.api_url', 'https://api.elfatoora.gov.tn');
        $this->apiKey = config('services.elfatoora.api_key');
        $this->certificatePath = config('services.elfatoora.certificate_path');
        $this->privateKeyPath = config('services.elfatoora.private_key_path');
    }

    /**
     * Signe électroniquement une facture
     */
    public function signInvoice(Invoice $invoice): array
    {
        try {
            // 1. Préparer les données de la facture au format El Fatoora
            $invoiceData = $this->prepareInvoiceData($invoice);

            // 2. Calculer le hash de la facture
            $hash = $this->calculateInvoiceHash($invoiceData);

            // 3. Signer le hash avec la clé privée
            $signature = $this->signHash($hash);

            // 4. Générer l'identifiant unique El Fatoora
            $elFatooraId = $this->generateElFatooraId($invoice);

            // 5. Générer le QR code conforme
            $qrCodeData = $this->generateQRCode($invoice, $signature, $elFatooraId);

            return [
                'success' => true,
                'elfatoora_id' => $elFatooraId,
                'signature' => $signature,
                'hash' => $hash,
                'qr_code' => $qrCodeData,
                'signed_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('El Fatoora signature failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Transmet une facture au système El Fatoora
     */
    public function transmitInvoice(Invoice $invoice, array $signatureData): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/api/v1/invoices/submit", [
                'invoice_data' => $this->prepareInvoiceData($invoice),
                'signature' => $signatureData['signature'],
                'hash' => $signatureData['hash'],
                'elfatoora_id' => $signatureData['elfatoora_id'],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'transmission_id' => $data['transmission_id'] ?? null,
                    'status' => $data['status'] ?? 'transmitted',
                    'validation_code' => $data['validation_code'] ?? null,
                    'transmitted_at' => now()->toIso8601String(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Transmission failed',
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('El Fatoora transmission failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Valide une facture auprès d'El Fatoora
     */
    public function validateInvoice(string $elFatooraId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->apiUrl}/api/v1/invoices/{$elFatooraId}/validate");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'valid' => $data['valid'] ?? false,
                    'status' => $data['status'] ?? 'unknown',
                    'validation_message' => $data['message'] ?? '',
                ];
            }

            return [
                'success' => false,
                'error' => 'Validation request failed',
            ];
        } catch (\Exception $e) {
            Log::error('El Fatoora validation failed', [
                'elfatoora_id' => $elFatooraId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prépare les données de la facture au format El Fatoora
     */
    private function prepareInvoiceData(Invoice $invoice): array
    {
        $company = $invoice->company;
        $client = $invoice->client;

        return [
            // Informations émetteur
            'issuer' => [
                'tax_id' => $company->vat_number ?? $company->registration_number,
                'name' => $company->name,
                'address' => $company->address,
                'city' => $company->city,
                'postal_code' => $company->postal_code,
                'country' => 'TN',
                'phone' => $company->phone,
                'email' => $company->email,
            ],

            // Informations client
            'recipient' => [
                'tax_id' => $client->vat_number ?? $client->registration_number ?? '',
                'name' => $client->name,
                'address' => $client->address ?? '',
                'city' => $client->city ?? '',
                'postal_code' => $client->postal_code ?? '',
                'country' => $client->country ?? 'TN',
                'phone' => $client->phone ?? '',
                'email' => $client->email ?? '',
            ],

            // Informations facture
            'invoice' => [
                'number' => $invoice->invoice_number,
                'date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'currency' => $invoice->currency,
                'type' => $this->mapInvoiceType($invoice->type),
            ],

            // Lignes de facture
            'items' => $invoice->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount ?? 0,
                    'subtotal' => $item->subtotal,
                    'vat_rate' => $item->vat_rate,
                    'vat_amount' => $item->vat_amount,
                    'total' => $item->total,
                ];
            })->toArray(),

            // Totaux
            'totals' => [
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount ?? 0,
                'vat_amount' => $invoice->vat_amount,
                'total' => $invoice->total_amount,
            ],

            // Informations de paiement
            'payment' => [
                'method' => $invoice->payment_method ?? 'virement',
                'terms' => $invoice->payment_terms ?? '30 jours',
                'bank_details' => $company->bank_details ?? [],
            ],

            // Notes
            'notes' => $invoice->notes ?? '',
        ];
    }

    /**
     * Calcule le hash SHA-256 de la facture
     */
    private function calculateInvoiceHash(array $invoiceData): string
    {
        // Créer une chaîne canonique des données de la facture
        $canonicalString = json_encode($invoiceData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Calculer le hash SHA-256
        return hash('sha256', $canonicalString);
    }

    /**
     * Signe le hash avec la clé privée du certificat
     */
    private function signHash(string $hash): string
    {
        if (!file_exists($this->privateKeyPath)) {
            // Mode simulation si le certificat n'existe pas
            return base64_encode("SIMULATED_SIGNATURE_{$hash}");
        }

        $privateKey = openssl_pkey_get_private(
            file_get_contents($this->privateKeyPath),
            config('services.elfatoora.private_key_password')
        );

        if (!$privateKey) {
            throw new \Exception('Failed to load private key');
        }

        openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        return base64_encode($signature);
    }

    /**
     * Génère l'identifiant unique El Fatoora
     */
    private function generateElFatooraId(Invoice $invoice): string
    {
        $company = $invoice->company;
        $taxId = $company->vat_number ?? $company->registration_number;

        // Format: [TaxID]-[InvoiceNumber]-[Timestamp]
        return sprintf(
            '%s-%s-%s',
            $taxId,
            $invoice->invoice_number,
            now()->format('YmdHis')
        );
    }

    /**
     * Génère le QR code conforme aux normes tunisiennes
     */
    private function generateQRCode(Invoice $invoice, string $signature, string $elFatooraId): string
    {
        // Données du QR code selon les spécifications El Fatoora
        $qrData = [
            'id' => $elFatooraId,
            'issuer_tax_id' => $invoice->company->vat_number ?? $invoice->company->registration_number,
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
            'total_amount' => $invoice->total_amount,
            'vat_amount' => $invoice->vat_amount,
            'signature' => substr($signature, 0, 50), // Premiers 50 caractères de la signature
        ];

        $qrString = json_encode($qrData, JSON_UNESCAPED_UNICODE);

        // Générer le QR code en base64
        return base64_encode(
            QrCode::format('png')
                ->size(200)
                ->errorCorrection('H')
                ->generate($qrString)
        );
    }

    /**
     * Mappe le type de facture ComptaPro vers El Fatoora
     */
    private function mapInvoiceType(string $type): string
    {
        return match ($type) {
            'invoice' => 'FACTURE',
            'quote' => 'DEVIS',
            'credit_note' => 'AVOIR',
            'proforma' => 'PROFORMA',
            default => 'FACTURE',
        };
    }

    /**
     * Vérifie le statut d'une facture dans El Fatoora
     */
    public function checkInvoiceStatus(string $elFatooraId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->apiUrl}/api/v1/invoices/{$elFatooraId}/status");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Status check failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Annule une facture dans El Fatoora
     */
    public function cancelInvoice(string $elFatooraId, string $reason): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/api/v1/invoices/{$elFatooraId}/cancel", [
                'reason' => $reason,
                'cancelled_at' => now()->toIso8601String(),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'cancellation_id' => $response->json()['cancellation_id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Cancellation failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Archive une facture (télécharge depuis El Fatoora)
     */
    public function archiveInvoice(string $elFatooraId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->apiUrl}/api/v1/invoices/{$elFatooraId}/archive");

            if ($response->successful()) {
                $archiveData = $response->json();

                return [
                    'success' => true,
                    'archive_url' => $archiveData['archive_url'] ?? null,
                    'archive_date' => $archiveData['archive_date'] ?? now()->toIso8601String(),
                    'pdf_url' => $archiveData['pdf_url'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Archive retrieval failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Traitement complet: signature + transmission
     */
    public function processInvoice(Invoice $invoice): array
    {
        // 1. Signer la facture
        $signatureResult = $this->signInvoice($invoice);

        if (!$signatureResult['success']) {
            return $signatureResult;
        }

        // 2. Transmettre au système El Fatoora
        $transmissionResult = $this->transmitInvoice($invoice, $signatureResult);

        // 3. Retourner le résultat combiné
        return array_merge($signatureResult, $transmissionResult);
    }

    /**
     * Test de connexion à l'API El Fatoora
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->apiUrl}/api/v1/health");

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $response->json()['message'] ?? 'Connection test completed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
