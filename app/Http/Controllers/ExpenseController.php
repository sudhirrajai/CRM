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
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string|max:255',
            'payment_mode' => 'nullable|in:upi,bank_transfer,neft_rtgs',
            'payment_mode_details' => 'nullable|array',
            'payment_mode_details.upi_id' => 'nullable|string|max:255',
            'payment_mode_details.account_number' => 'nullable|string|max:255',
            'payment_mode_details.ifsc_code' => 'nullable|string|max:255',
            'payment_mode_details.bank_name' => 'nullable|string|max:255',
            'payment_mode_details.account_holder' => 'nullable|string|max:255',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:weekly,monthly,yearly',
            'next_due_date' => 'nullable|required_if:is_recurring,true|date',
            'notes' => 'nullable|string',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB limit
        ]);

        if ($request->hasFile('invoice_file')) {
            $path = $request->file('invoice_file')->store('expenses/invoices', 'public');
            $validated['invoice_file_path'] = \Illuminate\Support\Facades\Storage::url($path);
        }

        $this->expenseRepository->create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }

    public function update(Request $request, $id)
    {
        $expense = $this->expenseRepository->find($id);
        
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string|max:255',
            'payment_mode' => 'nullable|in:upi,bank_transfer,neft_rtgs',
            'payment_mode_details' => 'nullable|array',
            'payment_mode_details.upi_id' => 'nullable|string|max:255',
            'payment_mode_details.account_number' => 'nullable|string|max:255',
            'payment_mode_details.ifsc_code' => 'nullable|string|max:255',
            'payment_mode_details.bank_name' => 'nullable|string|max:255',
            'payment_mode_details.account_holder' => 'nullable|string|max:255',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:weekly,monthly,yearly',
            'next_due_date' => 'nullable|required_if:is_recurring,true|date',
            'notes' => 'nullable|string',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if (!$validated['is_recurring']) {
            $validated['recurring_frequency'] = null;
            $validated['next_due_date'] = null;
        }

        if ($request->hasFile('invoice_file')) {
            // Delete old file
            if ($expense->invoice_file_path) {
                $oldPath = str_replace('/storage/', '', $expense->invoice_file_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('invoice_file')->store('expenses/invoices', 'public');
            $validated['invoice_file_path'] = \Illuminate\Support\Facades\Storage::url($path);
        }

        $this->expenseRepository->update($id, $validated);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy($id)
    {
        $this->expenseRepository->delete($id);

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
