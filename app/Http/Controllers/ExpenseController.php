<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\ExpenseCategoryRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    protected $expenseRepository;
    protected $categoryRepository;

    public function __construct(
        ExpenseRepositoryInterface $expenseRepository,
        ExpenseCategoryRepositoryInterface $categoryRepository
    ) {
        $this->expenseRepository = $expenseRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        return Inertia::render('Expenses/Index', [
            'expenses' => $this->expenseRepository->getAllWithCategory(),
            'categories' => $this->categoryRepository->all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $this->expenseRepository->create($request->all());

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $this->expenseRepository->update($id, $request->all());

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy($id)
    {
        $this->expenseRepository->delete($id);

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
