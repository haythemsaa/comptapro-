<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour s'assurer qu'une entreprise tunisienne est sélectionnée
 * Utilisé pour les routes spécifiques à la Tunisie
 */
class EnsureTunisianCompany
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Récupérer l'entreprise courante
        $companyId = $user->current_company_id;
        $company = \App\Models\Company::find($companyId);

        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'Aucune entreprise sélectionnée');
        }

        // Vérifier que l'entreprise est tunisienne
        if ($company->country !== 'TN') {
            return redirect()->route('dashboard')
                ->with('error', 'Cette fonctionnalité est réservée aux entreprises tunisiennes');
        }

        // Ajouter l'entreprise au request pour éviter de la recharger
        $request->attributes->set('company', $company);

        return $next($request);
    }
}
