<?php

namespace App\Repositories\Eloquent;

use App\Models\ExpenseCategory;
use App\Repositories\Interfaces\ExpenseCategoryRepositoryInterface;

class ExpenseCategoryRepository extends BaseRepository implements ExpenseCategoryRepositoryInterface
{
    public function __construct(ExpenseCategory $model)
    {
        parent::__construct($model);
    }
}
