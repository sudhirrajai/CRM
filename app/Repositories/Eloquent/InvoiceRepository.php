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

    public function create(array $attributes)
    {
        $selectedCrs = $attributes['selected_crs'] ?? [];
        unset($attributes['selected_crs']);

        $invoice = parent::create($attributes);

        if (!empty($selectedCrs)) {
            \App\Models\ChangeRequest::whereIn('id', $selectedCrs)->update([
                'invoice_id' => $invoice->id,
                'status' => 'invoiced'
            ]);

            // Create individual invoice items for CRs
            foreach ($selectedCrs as $crId) {
                $cr = \App\Models\ChangeRequest::find($crId);
                if ($cr) {
                    $invoice->items()->create([
                        'description' => ($invoice->project ? 'Project: ' . $invoice->project->name . ' - ' : '') . 'Change Request: ' . $cr->title,
                        'unit_price' => $cr->amount,
                        'quantity' => 1,
                        'total' => $cr->amount
                    ]);
                }
            }
        }

        return $invoice;
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
        return $this->model->with(['client', 'project', 'currency'])->latest()->limit($limit)->get();
    }

    public function getRecentByClient($clientId, $limit = 5)
    {
        return $this->model->where('client_id', $clientId)->with(['project', 'currency'])->latest()->limit($limit)->get();
    }

    public function getTotalRevenue()
    {
        return $this->model->where('status', 'paid')->sum('total_amount') ?? 0;
    }

    public function getOutstandingCount()
    {
        return $this->model->whereIn('status', ['sent', 'overdue'])->count();
    }

    public function getOutstandingAmount()
    {
        return $this->model->whereIn('status', ['sent', 'overdue'])->sum('total_amount') ?? 0;
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
