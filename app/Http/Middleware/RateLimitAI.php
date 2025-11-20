<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour limiter les appels aux services IA
 * Évite les abus et contrôle les coûts
 */
class RateLimitAI
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Non authentifié',
            ], 401);
        }

        // Clé unique par utilisateur et par type de service
        $key = $this->resolveRequestSignature($request, $user);

        // Vérifier le rate limit
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'error' => "Trop de requêtes. Veuillez réessayer dans {$seconds} secondes.",
                'retry_after' => $seconds,
            ], 429);
        }

        // Incrémenter le compteur
        RateLimiter::hit($key, $decayMinutes * 60);

        // Ajouter les headers de rate limit
        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $maxAttempts));

        return $response;
    }

    /**
     * Résout la signature unique de la requête
     */
    protected function resolveRequestSignature(Request $request, $user): string
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : 'unknown';

        // Différents quotas selon le type de service
        $serviceType = $this->getServiceType($routeName);

        return "ai-rate-limit:{$user->id}:{$serviceType}";
    }

    /**
     * Détermine le type de service IA
     */
    protected function getServiceType(string $routeName): string
    {
        if (str_contains($routeName, 'ocr')) {
            return 'ocr'; // Plus coûteux, limite plus stricte
        }

        if (str_contains($routeName, 'cash-flow') || str_contains($routeName, 'anomalies')) {
            return 'analysis'; // Analyse complexe
        }

        if (str_contains($routeName, 'assistant')) {
            return 'assistant'; // Assistant conversationnel
        }

        return 'general';
    }
}
