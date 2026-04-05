<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Currency;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\Interfaces\ServerRepositoryInterface;
use App\Repositories\Interfaces\ClientHostingRepositoryInterface;
use App\Repositories\Interfaces\LeadRepositoryInterface;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;

class DashboardService
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepo,
        protected ProjectRepositoryInterface $projectRepo,
        protected InvoiceRepositoryInterface $invoiceRepo,
        protected ServerRepositoryInterface $serverRepo,
        protected ClientHostingRepositoryInterface $hostingRepo,
        protected LeadRepositoryInterface $leadRepo,
        protected ExpenseRepositoryInterface $expenseRepo
    ) {}

    public function getAnalytics()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        // Get default currency
        $defaultCurrencyId = Setting::getValue('default_currency_id');
        $defaultCurrency = $defaultCurrencyId 
            ? Currency::find($defaultCurrencyId) 
            : Currency::first();

        if ($user && $user->hasRole('client') && $user->client_id) {
            $clientId = $user->client_id;
            return [
                'total_clients' => 1,
                'active_projects' => $this->projectRepo->getByClient($clientId)->where('status', 'in_progress')->count(),
                'total_invoices' => $this->invoiceRepo->getByClient($clientId)->count(),
                'servers_count' => $this->hostingRepo->getByClient($clientId)->count(),
                'recent_invoices' => $this->invoiceRepo->getRecentByClient($clientId),
                'recent_projects' => $this->projectRepo->getRecentByClient($clientId),
                'revenue_overview' => $this->invoiceRepo->getMonthlyRevenueByClient($clientId),
                'project_stats' => $this->projectRepo->getStatusCountsByClient($clientId),
                'default_currency' => $defaultCurrency,
                'is_admin' => false,
            ];
        }

        return [
            // Core stats
            'total_clients' => $this->clientRepo->all()->count(),
            'active_projects' => $this->projectRepo->all()->where('status', 'in_progress')->count(),
            'total_invoices' => $this->invoiceRepo->all()->count(),
            'servers_count' => $this->serverRepo->all()->count(),

            // Financial data
            'total_revenue' => $this->invoiceRepo->getTotalRevenue(),
            'total_expenses' => $this->expenseRepo->all()->sum('amount'),
            'outstanding_count' => $this->invoiceRepo->getOutstandingCount(),
            'outstanding_amount' => $this->invoiceRepo->getOutstandingAmount(),

            // Lists
            'recent_invoices' => $this->invoiceRepo->getRecent(),
            'recent_projects' => $this->projectRepo->getRecent(),
            'recent_clients' => $this->clientRepo->getRecent(),
            'recent_leads' => $this->leadRepo->getRecent(),

            // Charts
            'revenue_overview' => $this->invoiceRepo->getMonthlyRevenue(),
            'project_stats' => $this->projectRepo->getStatusCounts(),

            // Leads
            'total_leads' => $this->leadRepo->all()->count(),
            'leads_value' => $this->leadRepo->getTotalValue(),
            'leads_conversion_rate' => $this->leadRepo->getConversionRate(),

            // Currency
            'default_currency' => $defaultCurrency,
            
            'is_admin' => true,
        ];
    }
}
