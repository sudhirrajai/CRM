<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\ClientHostingRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialExport;

class ReportController extends Controller
{
    protected $invoiceRepository;
    protected $expenseRepository;
    protected $projectRepository;
    protected $clientRepository;
    protected $hostingRepository;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        ExpenseRepositoryInterface $expenseRepository,
        ProjectRepositoryInterface $projectRepository,
        ClientRepositoryInterface $clientRepository,
        ClientHostingRepositoryInterface $hostingRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->expenseRepository = $expenseRepository;
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
        $this->hostingRepository = $hostingRepository;
    }

    public function index()
    {
        $monthlyRevenue = DB::table('invoices')
            ->where('status', 'paid')
            ->whereYear('issue_date', Carbon::now()->year)
            ->select(DB::raw('MONTH(issue_date) as month'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('month')
            ->get();

        $monthlyExpenses = DB::table('expenses')
            ->whereYear('date', Carbon::now()->year)
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->get();

        $projectStats = DB::table('projects')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $clientGrowth = DB::table('clients')
            ->whereYear('created_at', Carbon::now()->year)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->get();

        return Inertia::render('Reports/Index', [
            'stats' => [
                'total_revenue' => DB::table('invoices')->where('status', 'paid')->sum('total_amount'),
                'total_expenses' => DB::table('expenses')->sum('amount'),
                'active_projects' => DB::table('projects')->where('status', 'in_progress')->count(),
                'total_clients' => DB::table('clients')->count(),
            ],
            'charts' => [
                'revenue' => $monthlyRevenue,
                'expenses' => $monthlyExpenses,
                'projects' => $projectStats,
                'clients' => $clientGrowth,
            ]
        ]);
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

    public function profitLoss(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        
        $revenueByMonth = DB::table('invoices')
            ->where('status', 'paid')
            ->whereYear('issue_date', $year)
            ->select(DB::raw('MONTH(issue_date) as month'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('month')
            ->get()->pluck('total', 'month')->toArray();

        $expensesByMonth = DB::table('expenses')
            ->whereYear('date', $year)
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->get()->pluck('total', 'month')->toArray();

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $rev = $revenueByMonth[$m] ?? 0;
            $exp = $expensesByMonth[$m] ?? 0;
            $data[] = [
                'month' => Carbon::create()->month($m)->format('M'),
                'revenue' => (float)$rev,
                'expenses' => (float)$exp,
                'profit' => (float)($rev - $exp)
            ];
        }

        return Inertia::render('Reports/ProfitLoss', [
            'data' => $data,
            'filters' => ['year' => $year]
        ]);
    }

    public function projects()
    {
        $statusCounts = DB::table('projects')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $upcomingDeadlines = DB::table('projects')
            ->where('status', '!=', 'completed')
            ->where('deadline', '>=', Carbon::now())
            ->orderBy('deadline')
            ->take(10)
            ->get();

        return Inertia::render('Reports/ProjectReport', [
            'statusCounts' => $statusCounts,
            'upcomingDeadlines' => $upcomingDeadlines,
        ]);
    }

    public function clients()
    {
        $clientAcquisition = DB::table('clients')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->get();

        $hostingDistribution = DB::table('client_hostings')
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        return Inertia::render('Reports/ClientReport', [
            'clientAcquisition' => $clientAcquisition,
            'hostingDistribution' => $hostingDistribution,
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'financial');
        $format = $request->get('format', 'xlsx');

        if ($type === 'financial') {
            return Excel::download(new FinancialExport, 'financial_report_' . date('Y-m-d') . '.' . $format);
        }

        return back()->with('error', 'Export type not supported yet.');
    }
}
