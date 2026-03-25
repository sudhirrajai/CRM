<?php

namespace App\Services;

use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\Interfaces\ServerRepositoryInterface;
use App\Repositories\Interfaces\ClientHostingRepositoryInterface;

class DashboardService
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepo,
        protected ProjectRepositoryInterface $projectRepo,
        protected InvoiceRepositoryInterface $invoiceRepo,
        protected ServerRepositoryInterface $serverRepo,
        protected ClientHostingRepositoryInterface $hostingRepo
    ) {}

    public function getAnalytics()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if ($user && $user->hasRole('client') && $user->client_id) {
            return [
                'total_clients' => 1,
                'active_projects' => collect($this->projectRepo->getByClient($user->client_id))->where('status', 'in_progress')->count(),
                'total_invoices' => collect($this->invoiceRepo->getByClient($user->client_id))->count(),
                'servers_count' => collect($this->hostingRepo->getByClient($user->client_id))->count(),
            ];
        }

        return [
            'total_clients' => collect($this->clientRepo->all())->count(),
            'active_projects' => collect($this->projectRepo->all())->where('status', 'in_progress')->count(),
            'total_invoices' => collect($this->invoiceRepo->all())->count(),
            'servers_count' => collect($this->serverRepo->all())->count(),
        ];
    }
}
