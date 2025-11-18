<?php

namespace App\Http\Controllers;

use App\Models\Modules\Core\Models\Company;
use App\Models\Modules\Invoicing\Models\{Customer, Invoice};
use App\Models\Modules\Products\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's companies with country information
        $companies = $user->companies()->with('country')->get();

        // Get current company (first one or from session)
        $currentCompanyId = session('current_company_id', $companies->first()?->id);
        $currentCompany = $companies->firstWhere('id', $currentCompanyId);

        // If no company found, use first available
        if (!$currentCompany && $companies->isNotEmpty()) {
            $currentCompany = $companies->first();
            session(['current_company_id' => $currentCompany->id]);
        }

        // Get statistics for current company
        $stats = [];
        if ($currentCompany) {
            $stats = [
                'total_customers' => Customer::where('company_id', $currentCompany->id)->count(),
                'total_invoices' => Invoice::where('company_id', $currentCompany->id)->count(),
                'draft_invoices' => Invoice::where('company_id', $currentCompany->id)
                    ->where('status', 'draft')->count(),
                'paid_invoices' => Invoice::where('company_id', $currentCompany->id)
                    ->where('status', 'paid')->count(),
                'overdue_invoices' => Invoice::where('company_id', $currentCompany->id)
                    ->where('status', 'overdue')->count(),
                'total_revenue' => Invoice::where('company_id', $currentCompany->id)
                    ->where('status', 'paid')
                    ->sum('total'),
                'pending_amount' => Invoice::where('company_id', $currentCompany->id)
                    ->whereIn('status', ['sent', 'overdue'])
                    ->sum('total'),
            ];

            // Recent invoices
            $recentInvoices = Invoice::where('company_id', $currentCompany->id)
                ->with('customer')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $stats['recent_invoices'] = $recentInvoices;

            // Recent customers
            $recentCustomers = Customer::where('company_id', $currentCompany->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $stats['recent_customers'] = $recentCustomers;
        }

        return Inertia::render('Dashboard', [
            'companies' => $companies,
            'currentCompany' => $currentCompany,
            'stats' => $stats,
        ]);
    }

    /**
     * Switch active company
     */
    public function switchCompany(Request $request, int $companyId)
    {
        $user = $request->user();

        // Verify user has access to this company
        $company = $user->companies()->find($companyId);

        if (!$company) {
            return redirect()->back()->with('error', 'Vous n\'avez pas accès à cette société.');
        }

        // Store in session
        session(['current_company_id' => $companyId]);

        return redirect()->route('dashboard')->with('success', 'Société changée avec succès.');
    }
}
