<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    public function getByClient($id)
    {
        return $this->model->where('client_id', $id)->get();
    }

    public function getByProject($id)
    {
        return $this->model->where('project_id', $id)->get();
    }

    public function getRecent($limit = 5)
    {
        return $this->model->with(['client', 'project'])->latest()->limit($limit)->get();
    }

    public function getRecentByClient($clientId, $limit = 5)
    {
        return $this->model->where('client_id', $clientId)->with(['project'])->latest()->limit($limit)->get();
    }

    public function getMonthlyRevenue($months = 12)
    {
        return $this->model->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as month, SUM(total_amount) as total')
            ->where('issue_date', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function getMonthlyRevenueByClient($clientId, $months = 12)
    {
        return $this->model->where('client_id', $clientId)
            ->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as month, SUM(total_amount) as total')
            ->where('issue_date', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
