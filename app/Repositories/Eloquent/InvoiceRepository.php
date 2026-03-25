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
}
