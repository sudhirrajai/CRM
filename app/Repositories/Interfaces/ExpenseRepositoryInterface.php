<?php

namespace App\Repositories\Interfaces;

interface ExpenseRepositoryInterface extends BaseRepositoryInterface
{
    public function getByCategory($categoryId);
    public function getRecent($limit = 5);
    public function getAllWithCategory();
}
