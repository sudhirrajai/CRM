<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;

class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    public function getByClient($id)
    {
        return $this->model->where('client_id', $id)->get();
    }

    public function getRecent($limit = 5)
    {
        return $this->model->with('client')->latest()->limit($limit)->get();
    }

    public function getRecentByClient($clientId, $limit = 5)
    {
        return $this->model->where('client_id', $clientId)->latest()->limit($limit)->get();
    }

    public function getStatusCounts()
    {
        return $this->model->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();
    }

    public function getStatusCountsByClient($clientId)
    {
        return $this->model->where('client_id', $clientId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();
    }
}
