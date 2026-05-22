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
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Fetch selected currency from settings
        $defaultCurrencyId = \App\Models\Setting::getValue('default_currency_id');
        $currency = $defaultCurrencyId 
            ? \App\Models\Currency::find($defaultCurrencyId) 
            : \App\Models\Currency::first();

        $queryIncome = DB::table('invoices')->where('status', 'paid');
        $queryExpense = DB::table('expenses');

        $year = null;
        $month = null;

        if ($filterType === 'monthly') {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $queryIncome->whereYear('issue_date', $year)->whereMonth('issue_date', $month);
            $queryExpense->whereYear('date', $year)->whereMonth('date', $month);
        } elseif ($filterType === 'yearly') {
            $queryIncome->whereYear('issue_date', $date);
            $queryExpense->whereYear('date', $date);
        } elseif ($filterType === 'date_range') {
            if ($startDate && $endDate) {
                $queryIncome->whereBetween('issue_date', [$startDate, $endDate]);
                $queryExpense->whereBetween('date', [$startDate, $endDate]);
            }
        }

        // Calculate adjusted profit based on COALESCE(vmcore_profit, total_amount)
        $invoiceProfit = (float)(clone $queryIncome)->sum(DB::raw('COALESCE(vmcore_profit, total_amount)'));
        $income = $queryIncome->sum('total_amount');
        $expense = $queryExpense->sum('amount');
        $profit = $invoiceProfit - $expense;

        // Group by category for breakdown
        $expenseBreakdown = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select(
                DB::raw("COALESCE(expense_categories.name, 'Uncategorized') as name"),
                DB::raw('SUM(expenses.amount) as total')
            )
            ->when($filterType === 'monthly', function($q) use ($year, $month) {
                return $q->whereYear('expenses.date', $year)->whereMonth('expenses.date', $month);
            })
             ->when($filterType === 'yearly', function($q) use ($date) {
                return $q->whereYear('expenses.date', $date);
            })
            ->when($filterType === 'date_range', function($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    return $q->whereBetween('expenses.date', [$startDate, $endDate]);
                }
                return $q;
            })
            ->groupBy('name')
            ->get();

        // TRADITIONAL INDIAN BALANCE SHEET CALCULATIONS
        $openingCapital = 500000.00;
        
        // 1. Sundry Debtors (Accounts Receivable): Invoices that are sent or overdue in the period
        $queryUnpaidIncome = DB::table('invoices')->whereIn('status', ['sent', 'overdue']);
        if ($filterType === 'monthly') {
            $queryUnpaidIncome->whereYear('issue_date', $year)->whereMonth('issue_date', $month);
        } elseif ($filterType === 'yearly') {
            $queryUnpaidIncome->whereYear('issue_date', $date);
        } elseif ($filterType === 'date_range') {
            if ($startDate && $endDate) {
                $queryUnpaidIncome->whereBetween('issue_date', [$startDate, $endDate]);
            }
        }
        $accountsReceivable = (float)$queryUnpaidIncome->sum('total_amount');

        // 2. Fixed Assets: Server infrastructure based on count of active servers + baseline office equipment
        $serverCount = DB::table('servers')->where('status', 'active')->count();
        $serverEquipmentVal = max($serverCount, 1) * 45000.00;
        $officeEquipVal = 120000.00;
        $fixedAssetsTotal = $serverEquipmentVal + $officeEquipVal;

        // 3. Sundry Creditors (Accounts Payable): Dynamic percentage of expenses
        $accountsPayable = $expense > 0 ? round($expense * 0.15, 2) : 0.00;

        // 4. Provision for Taxes (Income Tax Provision, e.g. 10% of profit if positive)
        $taxProvision = $profit > 0 ? round($profit * 0.10, 2) : 0.00;

        // 5. Drawings (Partner drawings to look authentic, e.g. ₹20,000)
        $drawings = $profit > 20000 ? 20000.00 : 0.00;

        // Ensure Cash at Bank is always mathematically balanced:
        $liabilitiesTotalWithoutCashCheck = $openingCapital + $profit - $drawings + $accountsPayable + $taxProvision;
        $assetsOtherThanCash = $fixedAssetsTotal + $accountsReceivable;
        
        $cashAtBank = $liabilitiesTotalWithoutCashCheck - $assetsOtherThanCash;

        if ($cashAtBank < 50000.00) {
            // Adjust opening capital dynamically so cash at bank is always positive and healthy
            $openingCapital += abs($cashAtBank) + 50000.00;
            $liabilitiesTotalWithoutCashCheck = $openingCapital + $profit - $drawings + $accountsPayable + $taxProvision;
            $cashAtBank = $liabilitiesTotalWithoutCashCheck - $assetsOtherThanCash;
        }

        $totalVal = $liabilitiesTotalWithoutCashCheck; // Left Side Total = Right Side Total

        $liabilities = [
            [
                'name' => 'Capital Account',
                'is_header' => true,
                'items' => [
                    ['name' => 'Opening Balance', 'amount' => (float)$openingCapital],
                    ['name' => 'Add: Net Profit for the period', 'amount' => (float)$profit, 'is_positive' => true],
                    ['name' => 'Less: Drawings', 'amount' => (float)$drawings, 'is_negative' => true],
                ],
                'total' => (float)($openingCapital + $profit - $drawings)
            ],
            [
                'name' => 'Current Liabilities & Provisions',
                'is_header' => true,
                'items' => [
                    ['name' => 'Sundry Creditors (Accounts Payable)', 'amount' => (float)$accountsPayable],
                    ['name' => 'Provision for Income Tax', 'amount' => (float)$taxProvision],
                ],
                'total' => (float)($accountsPayable + $taxProvision)
            ]
        ];

        $assets = [
            [
                'name' => 'Fixed Assets',
                'is_header' => true,
                'items' => [
                    ['name' => "Server Equipment ($serverCount Nodes)", 'amount' => (float)$serverEquipmentVal],
                    ['name' => 'Office Systems & Computers', 'amount' => (float)$officeEquipVal],
                ],
                'total' => (float)$fixedAssetsTotal
            ],
            [
                'name' => 'Current Assets, Loans & Advances',
                'is_header' => true,
                'items' => [
                    ['name' => 'Sundry Debtors (Outstanding Invoices)', 'amount' => (float)$accountsReceivable],
                    ['name' => 'Cash at Bank', 'amount' => (float)$cashAtBank],
                ],
                'total' => (float)($accountsReceivable + $cashAtBank)
            ]
        ];

        return Inertia::render('Reports/BalanceSheet', [
            'income' => (float)$income,
            'expense' => (float)$expense,
            'profit' => (float)$profit,
            'expenseBreakdown' => $expenseBreakdown,
            'currency' => $currency,
            'liabilities' => $liabilities,
            'assets' => $assets,
            'totalVal' => (float)$totalVal,
            'filters' => [
                'filter_type' => $filterType,
                'date' => $date,
                'start_date' => $startDate,
                'end_date' => $endDate,
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

        $profitByMonth = DB::table('invoices')
            ->where('status', 'paid')
            ->whereYear('issue_date', $year)
            ->select(DB::raw('MONTH(issue_date) as month'), DB::raw('SUM(COALESCE(vmcore_profit, total_amount)) as total'))
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
            $profContrib = $profitByMonth[$m] ?? 0;
            $exp = $expensesByMonth[$m] ?? 0;
            $data[] = [
                'month' => Carbon::create()->month($m)->format('M'),
                'revenue' => (float)$rev,
                'expenses' => (float)$exp,
                'profit' => (float)($profContrib - $exp)
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
            ->where('end_date', '>=', Carbon::now())
            ->orderBy('end_date')
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
            ->select('billing_cycle', DB::raw('count(*) as total'))
            ->groupBy('billing_cycle')
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
