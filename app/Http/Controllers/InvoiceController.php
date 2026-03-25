<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepo
    ) {}

    public function index()
    {
        $user = auth()->user();
        
        if (!$user->hasRole(['admin', 'staff'])) {
            $invoices = $this->invoiceRepo->getByClient($user->client_id)->load(['client', 'project', 'currency']);
        } else {
            $invoices = $this->invoiceRepo->all()->load(['client', 'project', 'currency']);
        }

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices
        ]);
    }

    public function create()
    {
        return Inertia::render('Invoices/Create', [
            'clients' => \App\Models\Client::where('status', 'active')->get(),
            'projects' => \App\Models\Project::where('status', 'in_progress')->get(),
            'currencies' => \App\Models\Currency::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'currency_id' => 'required|exists:currencies,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $invoice = $this->invoiceRepo->create($validated);

        if ($request->boolean('send_email')) {
            $invoiceModel = $invoice instanceof \App\Models\Invoice ? $invoice : \App\Models\Invoice::where('invoice_number', $validated['invoice_number'])->first();
            if ($invoiceModel) {
                $invoiceModel->load(['client', 'project', 'currency', 'items']);
                $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoiceModel]);
                Mail::to($invoiceModel->client->email)->send(new InvoiceMail($invoiceModel, $pdf->output()));
            }
        }

        return redirect()->route('invoices.index');
    }

    public function show($id)
    {
        $invoice = $this->invoiceRepo->find($id)->load(['client', 'project', 'currency', 'items']);
        $user = auth()->user();

        if (!$user->hasRole(['admin', 'staff']) && $invoice->client_id !== $user->client_id) {
            abort(403);
        }

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Invoices/Edit', [
            'invoice' => $this->invoiceRepo->find($id),
            'clients' => \App\Models\Client::where('status', 'active')->get(),
            'projects' => \App\Models\Project::where('status', 'in_progress')->get(),
            'currencies' => \App\Models\Currency::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'currency_id' => 'required|exists:currencies,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number,'.$id,
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $this->invoiceRepo->update($id, $validated);

        if ($request->boolean('send_email')) {
            $invoiceModel = \App\Models\Invoice::find($id)->load(['client', 'project', 'currency', 'items']);
            if ($invoiceModel) {
                $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoiceModel]);
                Mail::to($invoiceModel->client->email)->send(new InvoiceMail($invoiceModel, $pdf->output()));
            }
        }

        return redirect()->route('invoices.index');
    }

    public function destroy($id)
    {
        $this->invoiceRepo->delete($id);
        return redirect()->route('invoices.index');
    }

    public function viewPdf($id)
    {
        $invoice = $this->invoiceRepo->find($id)->load(['client', 'project', 'currency', 'items']);
        $user = auth()->user();

        if (!$user->hasRole(['admin', 'staff']) && $invoice->client_id !== $user->client_id) {
            abort(403);
        }

        $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoice]);
        return $pdf->stream('Invoice_' . $invoice->invoice_number . '.pdf');
    }

    public function downloadPdf($id)
    {
        $invoice = $this->invoiceRepo->find($id)->load(['client', 'project', 'currency', 'items']);
        $user = auth()->user();

        if (!$user->hasRole(['admin', 'staff']) && $invoice->client_id !== $user->client_id) {
            abort(403);
        }

        $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoice]);
        return $pdf->download('Invoice_' . $invoice->invoice_number . '.pdf');
    }
}
