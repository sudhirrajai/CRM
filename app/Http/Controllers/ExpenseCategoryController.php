<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ExpenseCategoryRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseCategoryController extends Controller
{
    protected $categoryRepository;

    public function __construct(ExpenseCategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        return Inertia::render('Expenses/Categories/Index', [
            'categories' => $this->categoryRepository->all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->categoryRepository->create($request->all());

        return redirect()->route('expense-categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->categoryRepository->update($id, $request->all());

        return redirect()->route('expense-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $this->categoryRepository->delete($id);

        return redirect()->route('expense-categories.index')->with('success', 'Category deleted successfully.');
    }
}
