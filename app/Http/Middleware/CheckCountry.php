<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour vérifier le pays de l'entreprise
 * Empêche l'accès aux routes spécifiques à un pays si l'entreprise n'est pas du bon pays
 */
class CheckCountry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $expectedCountry): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $company = $user->company;

        if (!$company) {
            abort(403, 'Aucune entreprise associée à ce compte');
        }

        // Vérifier le code pays
        $countryCode = strtoupper($expectedCountry);
        $companyCountry = strtoupper($company->country_code ?? '');

        if ($companyCountry !== $countryCode) {
            abort(403, "Cette fonctionnalité est réservée aux entreprises {$this->getCountryName($countryCode)}. Votre entreprise est: {$this->getCountryName($companyCountry)}");
        }

        return $next($request);
    }

    /**
     * Obtenir le nom du pays depuis le code
     */
    protected function getCountryName(string $code): string
    {
        return match (strtoupper($code)) {
            'TN' => 'tunisiennes',
            'BE' => 'belges',
            'FR' => 'françaises',
            'MA' => 'marocaines',
            'DZ' => 'algériennes',
            default => $code,
        };
    }
}
