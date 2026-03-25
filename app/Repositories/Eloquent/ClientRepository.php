<?php

namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\Interfaces\ClientRepositoryInterface;

class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    public function __construct(Client $model)
    {
        parent::__construct($model);
    }

    public function getWithRelations()
    {
        return $this->model->with(['currency', 'projects', 'invoices', 'hostings'])->get();
    }

    public function getRecent($limit = 5)
    {
        return $this->model->latest()->limit($limit)->get();
    }
}
