<?php

namespace App\Repositories\Eloquent;

use App\Models\ClientHosting;
use App\Repositories\Interfaces\ClientHostingRepositoryInterface;

class ClientHostingRepository extends BaseRepository implements ClientHostingRepositoryInterface
{
    public function __construct(ClientHosting $model)
    {
        parent::__construct($model);
    }

    public function getByClient($id)
    {
        return $this->model->where('client_id', $id)->get();
    }
}
