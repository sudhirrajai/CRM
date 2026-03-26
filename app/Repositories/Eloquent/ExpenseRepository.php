<?php

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;

class ExpenseRepository extends BaseRepository implements ExpenseRepositoryInterface
{
    public function __construct(Expense $model)
    {
        parent::__construct($model);
    }

    public function getByCategory($categoryId)
    {
        return $this->model->where('category_id', $categoryId)->with('category')->get();
    }

    public function getRecent($limit = 5)
    {
        return $this->model->with('category')->latest()->limit($limit)->get();
    }
    
    public function getAllWithCategory()
    {
        return $this->model->with('category')->latest()->get();
    }
}
