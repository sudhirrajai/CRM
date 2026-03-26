<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ClientHostingRepositoryInterface;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;

class ClientHostingController extends Controller
{
    public function __construct(
        protected ClientHostingRepositoryInterface $hostingRepo,
        protected InvoiceRepositoryInterface $invoiceRepo
    ) {}

    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('client')) {
            $hostings = $this->hostingRepo->all()->where('client_id', $user->client_id)->load(['client', 'server', 'currency', 'project']);
        } else {
            $hostings = $this->hostingRepo->all()->load(['client', 'server', 'currency', 'project']);
        }

        return Inertia::render('Hostings/Index', [
            'hostings' => $hostings
        ]);
    }

    public function create()
    {
        return Inertia::render('Hostings/Create', [
            'clients' => \App\Models\Client::where('status', 'active')->get(),
            'servers' => \App\Models\Server::where('status', 'active')->get(),
            'currencies' => \App\Models\Currency::all(),
            'projects' => \App\Models\Project::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'server_id' => 'required|exists:servers,id',
            'project_id' => 'nullable|exists:projects,id',
            'domain' => 'required|string|max:255',
            'plan_details' => 'nullable|string',
            'price' => 'required|numeric',
            'currency_id' => 'required|exists:currencies,id',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
            'next_due_date' => 'nullable|date',
            'status' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $hosting = $this->hostingRepo->create($validated);

        // Auto-generate invoice
        $invoiceNumber = 'INV-H-' . strtoupper(substr(uniqid(), -6));
        $issueDate = Carbon::now();
        $dueDate = Carbon::now()->addDays(7); // Default 7 days due date

        $invoiceData = [
            'client_id' => $hosting->client_id,
            'project_id' => $hosting->project_id,
            'currency_id' => $hosting->currency_id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'sub_total' => $hosting->price,
            'total_amount' => $hosting->price,
            'status' => 'sent',
            'notes' => "Subscription Invoice for domain: " . $hosting->domain . "\nPlan: " . ($hosting->plan_details ?: 'Standard Plan'),
        ];

        $invoice = Invoice::create($invoiceData);

        // Add invoice item
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => "Hosting Subscription - " . $hosting->domain . " (" . ucfirst($hosting->billing_cycle) . ")",
            'quantity' => 1,
            'unit_price' => $hosting->price,
            'total' => $hosting->price,
        ]);

        // Send email with PDF
        $invoice->load(['client', 'project', 'currency', 'items']);
        $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoice]);
        Mail::to($invoice->client->email)->send(new InvoiceMail($invoice, $pdf->output()));

        return redirect()->route('hostings.index');
    }

    public function show($id)
    {
        $hosting = $this->hostingRepo->find($id)->load(['client', 'server', 'currency', 'project']);
        $user = auth()->user();

        if ($user->hasRole('client') && $hosting->client_id !== $user->client_id) {
            abort(403);
        }

        return Inertia::render('Hostings/Show', [
            'hosting' => $hosting
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Hostings/Edit', [
            'hosting' => $this->hostingRepo->find($id),
            'clients' => \App\Models\Client::where('status', 'active')->get(),
            'servers' => \App\Models\Server::where('status', 'active')->get(),
            'currencies' => \App\Models\Currency::all(),
            'projects' => \App\Models\Project::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'server_id' => 'required|exists:servers,id',
            'project_id' => 'nullable|exists:projects,id',
            'domain' => 'required|string|max:255',
            'plan_details' => 'nullable|string',
            'price' => 'required|numeric',
            'currency_id' => 'required|exists:currencies,id',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
            'next_due_date' => 'nullable|date',
            'status' => 'required|string',
            'reason' => 'required_if:status,suspended,terminated,cancelled|nullable|string',
        ]);

        $this->hostingRepo->update($id, $validated);
        return redirect()->route('hostings.index');
    }

    public function destroy($id)
    {
        $this->hostingRepo->delete($id);
        return redirect()->route('hostings.index');
    }
}
