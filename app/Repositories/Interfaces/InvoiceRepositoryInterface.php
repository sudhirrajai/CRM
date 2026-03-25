<?php

namespace App\Repositories\Interfaces;

interface InvoiceRepositoryInterface extends BaseRepositoryInterface
{
    public function getByClient($id);
    public function getByProject($id);
    public function getRecent($limit = 5);
    public function getRecentByClient($clientId, $limit = 5);
    public function getMonthlyRevenue($months = 12);
    public function getMonthlyRevenueByClient($clientId, $months = 12);
}
