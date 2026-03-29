<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FinancialExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        $invoices = DB::table('invoices')
            ->select('issue_date', 'invoice_number', 'total_amount', DB::raw("'Income' as type"), 'status')
            ->get();

        $expenses = DB::table('expenses')
            ->select('date as issue_date', DB::raw("'' as invoice_number"), 'amount as total_amount', DB::raw("'Expense' as type"), DB::raw("'paid' as status"))
            ->get();

        return $invoices->concat($expenses)->sortByDesc('issue_date');
    }

    public function headings(): array
    {
        return [
            'Date',
            'Reference',
            'Amount',
            'Type',
            'Status'
        ];
    }

    public function map($row): array
    {
        return [
            $row->issue_date,
            $row->invoice_number ?: 'N/A',
            $row->total_amount,
            $row->type,
            $row->status
        ];
    }
}
