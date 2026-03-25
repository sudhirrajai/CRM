<?php

namespace App\Repositories\Interfaces;

interface ProjectRepositoryInterface extends BaseRepositoryInterface
{
    public function getByClient($id);
}
