<?php

namespace App\Repositories\Interfaces;

interface InvoiceRepositoryInterface extends BaseRepositoryInterface
{
    public function getByClient($id);
    public function getByProject($id);
}
