<?php

namespace App\Repositories\Interfaces;

interface ClientHostingRepositoryInterface extends BaseRepositoryInterface
{
    public function getByClient($id);
}
