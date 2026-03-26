<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $invoiceRepository;
    protected $expenseRepository;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        ExpenseRepositoryInterface $expenseRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->expenseRepository = $expenseRepository;
    }

    public function balanceSheet(Request $request)
    {
        $filterType = $request->get('filter_type', 'monthly'); // monthly, yearly, date_range
        $date = $request->get('date', Carbon::now()->format('Y-m'));

        $queryIncome = DB::table('invoices')->where('status', 'paid');
        $queryExpense = DB::table('expenses');

        if ($filterType === 'monthly') {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $queryIncome->whereYear('issue_date', $year)->whereMonth('issue_date', $month);
            $queryExpense->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($filterType === 'yearly') {
            $queryIncome->whereYear('issue_date', $date);
            $queryExpense->whereYear('date', $date);
        } elseif ($filterType === 'date_range') {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            if ($startDate && $endDate) {
                $queryIncome->whereBetween('issue_date', [$startDate, $endDate]);
                $queryExpense->whereBetween('date', [$startDate, $endDate]);
            }
        }

        $income = $queryIncome->sum('total_amount');
        $expense = $queryExpense->sum('amount');
        $profit = $income - $expense;

        // Group by category for breakdown
        $expenseBreakdown = DB::table('expenses')
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->when($filterType === 'monthly', function($q) use ($date) {
                return $q->whereYear('expenses.date', substr($date, 0, 4))->whereMonth('expenses.date', substr($date, 5, 2));
            })
             ->when($filterType === 'yearly', function($q) use ($date) {
                return $q->whereYear('expenses.date', $date);
            })
            ->groupBy('expense_categories.name')
            ->get();

        return Inertia::render('Reports/BalanceSheet', [
            'income' => (float)$income,
            'expense' => (float)$expense,
            'profit' => (float)$profit,
            'expenseBreakdown' => $expenseBreakdown,
            'filters' => [
                'filter_type' => $filterType,
                'date' => $date,
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
            ]
        ]);
    }
}
