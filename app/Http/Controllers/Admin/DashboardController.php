<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Contrôleur du dashboard admin
 * Vue d'ensemble complète de toutes les entreprises
 */
class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques globales
        $stats = [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('is_active', true)->count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),

            // Entreprises par pays
            'companies_tunisia' => Company::where('country_code', 'TN')->count(),
            'companies_belgium' => Company::where('country_code', 'BE')->count(),

            // Croissance
            'new_companies_this_month' => Company::whereMonth('created_at', now()->month)->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        // Dernières entreprises
        $recentCompanies = Company::with('users')
            ->latest()
            ->take(10)
            ->get();

        // Derniers utilisateurs
        $recentUsers = User::with('company')
            ->latest()
            ->take(10)
            ->get();

        // Statistiques par mois (12 derniers mois)
        $monthlyStats = $this->getMonthlyStats();

        // Entreprises par secteur
        $companiesBySector = Company::select('sector', DB::raw('count(*) as count'))
            ->groupBy('sector')
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentCompanies',
            'recentUsers',
            'monthlyStats',
            'companiesBySector'
        ));
    }

    /**
     * Statistiques mensuelles
     */
    protected function getMonthlyStats()
    {
        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $months[] = [
                'month' => $date->format('M Y'),
                'companies' => Company::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        return $months;
    }

    /**
     * Statistiques en temps réel (API)
     */
    public function realtimeStats()
    {
        return response()->json([
            'companies' => [
                'total' => Company::count(),
                'active' => Company::where('is_active', true)->count(),
                'tunisia' => Company::where('country_code', 'TN')->count(),
                'belgium' => Company::where('country_code', 'BE')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'online' => User::where('last_activity', '>=', now()->subMinutes(5))->count(),
            ],
            'activity' => [
                'today' => $this->getTodayActivity(),
            ]
        ]);
    }

    protected function getTodayActivity()
    {
        return [
            'companies_created' => Company::whereDate('created_at', today())->count(),
            'users_created' => User::whereDate('created_at', today())->count(),
            'users_logged_in' => User::whereDate('last_login', today())->count(),
        ];
    }
}
