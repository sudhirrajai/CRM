<?php

namespace App\Repositories\Interfaces;

interface ProjectRepositoryInterface extends BaseRepositoryInterface
{
    public function getByClient($id);
    public function getRecent($limit = 5);
    public function getRecentByClient($clientId, $limit = 5);
    public function getStatusCounts();
    public function getStatusCountsByClient($clientId);
}
