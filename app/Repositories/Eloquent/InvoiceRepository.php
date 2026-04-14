<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    public function create(array $attributes)
    {
        $items = $attributes['items'] ?? [];
        $selectedCrs = $attributes['selected_crs'] ?? [];
        $sharedClientIds = $attributes['shared_client_ids'] ?? [];
        $extraRecipients = $attributes['extra_recipients'] ?? [];
        unset($attributes['items']);
        unset($attributes['selected_crs']);
        unset($attributes['shared_client_ids']);
        unset($attributes['extra_recipients']);

        return DB::transaction(function () use ($attributes, $items, $selectedCrs, $sharedClientIds, $extraRecipients) {
            $invoice = parent::create($attributes);

            if (!empty($selectedCrs)) {
                \App\Models\ChangeRequest::whereIn('id', $selectedCrs)->update([
                    'invoice_id' => $invoice->id,
                    'status' => 'invoiced'
                ]);

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

            foreach ($items as $item) {
                if (blank($item['description'] ?? null)) {
                    continue;
                }

                $invoice->items()->create([
                    'description' => $item['description'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total']
                ]);
            }

            $this->syncInvoiceRecipients($invoice, $sharedClientIds, $extraRecipients);

            return $invoice->load(['items', 'sharedClients', 'emailRecipients']);
        });
    }

    public function update($id, array $attributes)
    {
        $items = $attributes['items'] ?? [];
        $sharedClientIds = $attributes['shared_client_ids'] ?? [];
        $extraRecipients = $attributes['extra_recipients'] ?? [];
        unset($attributes['items']);
        unset($attributes['shared_client_ids']);
        unset($attributes['extra_recipients']);

        return DB::transaction(function () use ($id, $attributes, $items, $sharedClientIds, $extraRecipients) {
            $record = parent::update($id, $attributes);

            if (is_array($items)) {
                $record->items()->delete();

                foreach ($items as $item) {
                    if (blank($item['description'] ?? null)) {
                        continue;
                    }

                    $record->items()->create([
                        'description' => $item['description'],
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'total' => $item['total']
                    ]);
                }
            }

            $this->syncInvoiceRecipients($record, $sharedClientIds, $extraRecipients);

            return $record->load(['items', 'sharedClients', 'emailRecipients']);
        });
    }

    private function syncInvoiceRecipients(Invoice $invoice, array $sharedClientIds, array $extraRecipients): void
    {
        $primaryClientId = $invoice->client_id;

        $normalizedClientIds = collect($sharedClientIds)
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->reject(fn ($id) => $id === (string) $primaryClientId)
            ->unique()
            ->values()
            ->all();
        $invoice->sharedClients()->sync($normalizedClientIds);

        $normalizedEmails = collect($extraRecipients)
            ->filter(fn ($email) => is_string($email) && filled(trim($email)))
            ->map(fn ($email) => strtolower(trim($email)))
            ->unique()
            ->values();

        $invoice->emailRecipients()->delete();
        foreach ($normalizedEmails as $email) {
            $invoice->emailRecipients()->create(['email' => $email]);
        }
    }
    public function getByClient($id)
    {
        return $this->model
            ->where(function ($query) use ($id) {
                $query->where('client_id', $id)
                    ->orWhereHas('sharedClients', function ($sharedQuery) use ($id) {
                        $sharedQuery->whereKey($id);
                    });
            })
            ->get();
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
        return $this->model
            ->where(function ($query) use ($clientId) {
                $query->where('client_id', $clientId)
                    ->orWhereHas('sharedClients', function ($sharedQuery) use ($clientId) {
                        $sharedQuery->whereKey($clientId);
                    });
            })
            ->with(['project', 'currency'])
            ->latest()
            ->limit($limit)
            ->get();
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
        return $this->model
            ->where(function ($query) use ($clientId) {
                $query->where('client_id', $clientId)
                    ->orWhereHas('sharedClients', function ($sharedQuery) use ($clientId) {
                        $sharedQuery->whereKey($clientId);
                    });
            })
            ->selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as month, SUM(total_amount) as total')
            ->where('issue_date', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
