<?php

namespace App\Http\Controllers\ElFatoora;

use App\Http\Controllers\Controller;
use App\Models\Invoicing\Invoice;
use App\Services\ElFatoora\ElFatooraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ElFatooraController extends Controller
{
    private ElFatooraService $elFatooraService;

    public function __construct(ElFatooraService $elFatooraService)
    {
        $this->elFatooraService = $elFatooraService;
    }

    /**
     * Signe une facture électroniquement
     */
    public function signInvoice(Request $request, int $invoiceId)
    {
        $companyId = $request->user()->current_company_id;

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['company', 'client', 'items'])
            ->findOrFail($invoiceId);

        // Vérifier que la facture n'est pas déjà signée
        if ($invoice->elfatoora_signed_at) {
            return response()->json([
                'success' => false,
                'error' => 'Cette facture est déjà signée',
            ], 400);
        }

        // Signer la facture
        $result = $this->elFatooraService->signInvoice($invoice);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        // Mettre à jour la facture
        $invoice->update([
            'elfatoora_id' => $result['elfatoora_id'],
            'elfatoora_signature' => $result['signature'],
            'elfatoora_hash' => $result['hash'],
            'elfatoora_qr_code' => $result['qr_code'],
            'elfatoora_signed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Facture signée avec succès',
            'data' => $result,
        ]);
    }

    /**
     * Transmet une facture au système El Fatoora
     */
    public function transmitInvoice(Request $request, int $invoiceId)
    {
        $companyId = $request->user()->current_company_id;

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['company', 'client', 'items'])
            ->findOrFail($invoiceId);

        // Vérifier que la facture est signée
        if (!$invoice->elfatoora_signed_at) {
            return response()->json([
                'success' => false,
                'error' => 'La facture doit être signée avant d\'être transmise',
            ], 400);
        }

        // Préparer les données de signature
        $signatureData = [
            'elfatoora_id' => $invoice->elfatoora_id,
            'signature' => $invoice->elfatoora_signature,
            'hash' => $invoice->elfatoora_hash,
        ];

        // Transmettre
        $result = $this->elFatooraService->transmitInvoice($invoice, $signatureData);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        // Mettre à jour la facture
        $invoice->update([
            'elfatoora_transmission_id' => $result['transmission_id'] ?? null,
            'elfatoora_validation_code' => $result['validation_code'] ?? null,
            'elfatoora_transmitted_at' => now(),
            'elfatoora_status' => $result['status'] ?? 'transmitted',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Facture transmise avec succès',
            'data' => $result,
        ]);
    }

    /**
     * Processus complet: signature + transmission
     */
    public function processInvoice(Request $request, int $invoiceId)
    {
        $companyId = $request->user()->current_company_id;

        $invoice = Invoice::where('company_id', $companyId)
            ->with(['company', 'client', 'items'])
            ->findOrFail($invoiceId);

        // Traitement complet
        $result = $this->elFatooraService->processInvoice($invoice);

        if (!$result['success']) {
            Log::error('El Fatoora processing failed', [
                'invoice_id' => $invoiceId,
                'result' => $result,
            ]);

            return response()->json($result, 500);
        }

        // Mettre à jour la facture
        $invoice->update([
            'elfatoora_id' => $result['elfatoora_id'],
            'elfatoora_signature' => $result['signature'],
            'elfatoora_hash' => $result['hash'],
            'elfatoora_qr_code' => $result['qr_code'],
            'elfatoora_signed_at' => now(),
            'elfatoora_transmission_id' => $result['transmission_id'] ?? null,
            'elfatoora_validation_code' => $result['validation_code'] ?? null,
            'elfatoora_transmitted_at' => now(),
            'elfatoora_status' => $result['status'] ?? 'transmitted',
        ]);

        Log::info('El Fatoora processing succeeded', [
            'invoice_id' => $invoiceId,
            'elfatoora_id' => $result['elfatoora_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Facture traitée avec succès',
            'data' => $result,
        ]);
    }

    /**
     * Vérifie le statut d'une facture
     */
    public function checkStatus(Request $request, int $invoiceId)
    {
        $companyId = $request->user()->current_company_id;

        $invoice = Invoice::where('company_id', $companyId)
            ->findOrFail($invoiceId);

        if (!$invoice->elfatoora_id) {
            return response()->json([
                'success' => false,
                'error' => 'Facture non signée',
            ], 400);
        }

        $result = $this->elFatooraService->checkInvoiceStatus($invoice->elfatoora_id);

        return response()->json($result);
    }

    /**
     * Valide une facture auprès d'El Fatoora
     */
    public function validateInvoice(Request $request, int $invoiceId)
    {
        $companyId = $request->user()->current_company_id;

        $invoice = Invoice::where('company_id', $companyId)
            ->findOrFail($invoiceId);

        if (!$invoice->elfatoora_id) {
            return response()->json([
                'success' => false,
                'error' => 'Facture non signée',
            ], 400);
        }

        $result = $this->elFatooraService->validateInvoice($invoice->elfatoora_id);

        if ($result['success'] && $result['valid']) {
            $invoice->update([
                'elfatoora_status' => 'validated',
                'elfatoora_validated_at' => now(),
            ]);
        }

        return response()->json($result);
    }

    /**
     * Annule une facture dans El Fatoora
     */
    public function cancelInvoice(Request $request, int $invoiceId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $companyId = $request->user()->current_company_id;

        $invoice = Invoice::where('company_id', $companyId)
            ->findOrFail($invoiceId);

        if (!$invoice->elfatoora_id) {
            return response()->json([
                'success' => false,
                'error' => 'Facture non signée',
            ], 400);
        }

        $result = $this->elFatooraService->cancelInvoice(
            $invoice->elfatoora_id,
            $validated['reason']
        );

        if ($result['success']) {
            $invoice->update([
                'elfatoora_status' => 'cancelled',
                'elfatoora_cancelled_at' => now(),
                'elfatoora_cancellation_reason' => $validated['reason'],
            ]);
        }

        return response()->json($result);
    }

    /**
     * Archive une facture (récupère depuis El Fatoora)
     */
    public function archiveInvoice(Request $request, int $invoiceId)
    {
        $companyId = $request->user()->current_company_id;

        $invoice = Invoice::where('company_id', $companyId)
            ->findOrFail($invoiceId);

        if (!$invoice->elfatoora_id) {
            return response()->json([
                'success' => false,
                'error' => 'Facture non signée',
            ], 400);
        }

        $result = $this->elFatooraService->archiveInvoice($invoice->elfatoora_id);

        if ($result['success']) {
            $invoice->update([
                'elfatoora_archived_at' => now(),
                'elfatoora_archive_url' => $result['archive_url'] ?? null,
            ]);
        }

        return response()->json($result);
    }

    /**
     * Test de connexion à l'API El Fatoora
     */
    public function testConnection(Request $request)
    {
        $result = $this->elFatooraService->testConnection();

        return response()->json($result);
    }

    /**
     * Page de tableau de bord El Fatoora
     */
    public function dashboard(Request $request)
    {
        $companyId = $request->user()->current_company_id;

        $stats = [
            'total_signed' => Invoice::where('company_id', $companyId)
                ->whereNotNull('elfatoora_signed_at')
                ->count(),
            'total_transmitted' => Invoice::where('company_id', $companyId)
                ->whereNotNull('elfatoora_transmitted_at')
                ->count(),
            'total_validated' => Invoice::where('company_id', $companyId)
                ->where('elfatoora_status', 'validated')
                ->count(),
            'total_cancelled' => Invoice::where('company_id', $companyId)
                ->where('elfatoora_status', 'cancelled')
                ->count(),
        ];

        $recentInvoices = Invoice::where('company_id', $companyId)
            ->whereNotNull('elfatoora_signed_at')
            ->with('client')
            ->orderBy('elfatoora_signed_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('ElFatoora/Dashboard', [
            'stats' => $stats,
            'recent_invoices' => $recentInvoices,
        ]);
    }

    /**
     * Page des paramètres El Fatoora
     */
    public function settings(Request $request)
    {
        $companyId = $request->user()->current_company_id;
        $company = \App\Models\Company::findOrFail($companyId);

        $settings = [
            'enabled' => config('tunisia.elfatoora.enabled', false),
            'auto_transmit' => config('tunisia.elfatoora.auto_transmit', false),
            'api_url' => config('tunisia.elfatoora.api_url'),
            'certificate_configured' => !empty(config('tunisia.elfatoora.certificate_path')),
        ];

        return Inertia::render('ElFatoora/Settings', [
            'settings' => $settings,
            'company' => $company,
        ]);
    }

    /**
     * Met à jour les paramètres El Fatoora
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'auto_transmit' => 'required|boolean',
        ]);

        // TODO: Sauvegarder les paramètres dans la configuration de l'entreprise

        return redirect()->route('elfatoora.settings')
            ->with('success', 'Paramètres mis à jour avec succès');
    }

    /**
     * Upload du certificat électronique
     */
    public function uploadCertificate(Request $request)
    {
        $validated = $request->validate([
            'certificate' => 'required|file|mimes:pem,crt|max:2048',
            'private_key' => 'required|file|mimes:pem,key|max:2048',
            'password' => 'required|string',
        ]);

        $companyId = $request->user()->current_company_id;

        // Sauvegarder les fichiers
        $certPath = $request->file('certificate')->store("elfatoora/certificates/{$companyId}");
        $keyPath = $request->file('private_key')->store("elfatoora/certificates/{$companyId}");

        // TODO: Sauvegarder les chemins et le mot de passe (chiffré) dans la base de données

        return response()->json([
            'success' => true,
            'message' => 'Certificat uploadé avec succès',
        ]);
    }
}
