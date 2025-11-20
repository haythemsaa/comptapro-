<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour vérifier que El Fatoora est correctement configuré
 */
class CheckElFatooraConfiguration
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $companyId = $user->current_company_id;
        $company = \App\Models\Company::find($companyId);

        if (!$company) {
            return $this->errorResponse('Entreprise introuvable', 404);
        }

        // Vérifier la configuration El Fatoora
        $settings = $company->settings ?? [];

        if (!isset($settings['elfatoora_enabled']) || !$settings['elfatoora_enabled']) {
            return $this->errorResponse(
                'El Fatoora n\'est pas activé pour cette entreprise. Veuillez configurer El Fatoora dans les paramètres.',
                403
            );
        }

        if (!isset($settings['elfatoora_api_key']) || empty($settings['elfatoora_api_key'])) {
            return $this->errorResponse(
                'Clé API El Fatoora manquante. Veuillez configurer votre clé API dans les paramètres.',
                403
            );
        }

        if (!isset($settings['elfatoora_certificate_path']) || empty($settings['elfatoora_certificate_path'])) {
            return $this->errorResponse(
                'Certificat El Fatoora manquant. Veuillez télécharger votre certificat dans les paramètres.',
                403
            );
        }

        // Vérifier que le certificat existe
        $certPath = storage_path('app/certificates/elfatoora/' . $settings['elfatoora_certificate_path']);
        if (!file_exists($certPath)) {
            return $this->errorResponse(
                'Le fichier de certificat El Fatoora est introuvable. Veuillez le télécharger à nouveau.',
                403
            );
        }

        return $next($request);
    }

    /**
     * Retourne une réponse d'erreur appropriée
     */
    private function errorResponse(string $message, int $status): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => $message,
            ], $status);
        }

        return redirect()->route('settings.elfatoora')
            ->with('error', $message);
    }
}
