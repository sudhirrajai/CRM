<?php

namespace App\Repositories\Interfaces;

interface ClientRepositoryInterface extends BaseRepositoryInterface
{
    public function getWithRelations();
    public function getRecent($limit = 5);
}
