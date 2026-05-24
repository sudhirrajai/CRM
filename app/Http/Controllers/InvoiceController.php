<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use App\Mail\HostingSuspensionMail;
use App\Models\ClientHosting;
use App\Models\Invoice;
use App\Models\ChangeRequest;
use App\Support\InvoiceRecipientResolver;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepo,
        protected InvoiceRecipientResolver $invoiceRecipientResolver
    ) {}

    public function index()
    {
        $user = auth()->user();
        
        if (!$user->hasRole(['admin', 'staff'])) {
            $invoices = $this->invoiceRepo->getByClient($user->client_id)->load(['client', 'project', 'currency']);
            $invoices->each(function ($invoice) {
                $invoice->makeHidden(['vmcore_profit']);
            });
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
            'projects' => \App\Models\Project::with(['changeRequests' => fn($q) => $q->where('status', 'pending')])->get(),
            'hostings' => ClientHosting::with(['project:id,name', 'currency:id,code,symbol,symbol_position'])
                ->get(['id', 'client_id', 'project_id', 'currency_id', 'domain', 'plan_details', 'price', 'billing_cycle']),
            'currencies' => \App\Models\Currency::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'shared_client_ids' => 'nullable|array',
            'shared_client_ids.*' => 'distinct|exists:clients,id',
            'extra_recipients' => 'nullable|array',
            'extra_recipients.*' => 'email',
            'project_id' => 'nullable|exists:projects,id',
            'currency_id' => 'required|exists:currencies,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'payment_mode' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'payment_note' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',
            'vmcore_profit' => 'nullable|numeric|min:0',
            'selected_crs' => 'nullable|array',
            'selected_crs.*' => 'exists:change_requests,id',
            'items' => 'nullable|array',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
        ]);

        $this->syncInvoiceTotals($validated);

        $invoice = $this->invoiceRepo->create($validated);

        if ($request->boolean('send_email')) {
            $invoiceModel = $invoice instanceof \App\Models\Invoice ? $invoice : \App\Models\Invoice::where('invoice_number', $validated['invoice_number'])->first();
            if ($invoiceModel) {
                $invoiceModel->load(['client', 'project', 'currency', 'items', 'sharedClients', 'emailRecipients']);
                $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoiceModel]);
                $recipients = $this->invoiceRecipientResolver->emails($invoiceModel);
                if (!empty($recipients)) {
                    Mail::to($recipients)->send(new InvoiceMail($invoiceModel, $pdf->output()));
                }
            }
        }

        return redirect()->route('invoices.index');
    }

    public function show($id)
    {
        $invoice = $this->invoiceRepo->find($id)->load(['client', 'project', 'currency', 'items', 'sharedClients', 'emailRecipients']);
        $user = auth()->user();

        if (!$user->hasRole(['admin', 'staff']) && !$this->canClientAccessInvoice($invoice, $user->client_id)) {
            abort(403);
        }

        if (!$user->hasRole(['admin', 'staff'])) {
            $invoice->makeHidden(['vmcore_profit']);
        }

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    public function edit($id)
    {
        $invoice = $this->invoiceRepo->find($id)->load(['items', 'sharedClients', 'emailRecipients']);
        
        // Format dates for HTML5 date input
        $invoice->issue_date_formatted = $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : '';
        $invoice->due_date_formatted = $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '';

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'clients' => \App\Models\Client::where('status', 'active')->get(),
            'projects' => \App\Models\Project::where('status', 'in_progress')->get(),
            'hostings' => ClientHosting::with(['project:id,name', 'currency:id,code,symbol,symbol_position'])
                ->get(['id', 'client_id', 'project_id', 'currency_id', 'domain', 'plan_details', 'price', 'billing_cycle']),
            'currencies' => \App\Models\Currency::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'shared_client_ids' => 'nullable|array',
            'shared_client_ids.*' => 'distinct|exists:clients,id',
            'extra_recipients' => 'nullable|array',
            'extra_recipients.*' => 'email',
            'project_id' => 'nullable|exists:projects,id',
            'currency_id' => 'required|exists:currencies,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number,'.$id,
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'payment_mode' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'payment_note' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',
            'vmcore_profit' => 'nullable|numeric|min:0',
            'selected_crs' => 'nullable|array',
            'selected_crs.*' => 'exists:change_requests,id',
            'items' => 'nullable|array',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
        ]);

        $this->syncInvoiceTotals($validated);

        $this->invoiceRepo->update($id, $validated);

        if ($request->boolean('send_email')) {
            $invoiceModel = \App\Models\Invoice::find($id)->load(['client', 'project', 'currency', 'items', 'sharedClients', 'emailRecipients']);
            if ($invoiceModel) {
                $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoiceModel]);
                $recipients = $this->invoiceRecipientResolver->emails($invoiceModel);
                if (!empty($recipients)) {
                    Mail::to($recipients)->send(new InvoiceMail($invoiceModel, $pdf->output()));
                }
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
        $invoice = $this->invoiceRepo->find($id)->load(['client', 'project', 'currency', 'items', 'sharedClients', 'emailRecipients']);
        $user = auth()->user();

        if (!$user->hasRole(['admin', 'staff']) && !$this->canClientAccessInvoice($invoice, $user->client_id)) {
            abort(403);
        }

        $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoice]);
        return $pdf->stream('Invoice_' . $invoice->invoice_number . '.pdf');
    }

    public function downloadPdf($id)
    {
        $invoice = $this->invoiceRepo->find($id)->load(['client', 'project', 'currency', 'items', 'sharedClients', 'emailRecipients']);
        $user = auth()->user();

        if (!$user->hasRole(['admin', 'staff']) && !$this->canClientAccessInvoice($invoice, $user->client_id)) {
            abort(403);
        }

        $pdf = Pdf::loadView('invoices.template', ['invoice' => $invoice]);
        return $pdf->download('Invoice_' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Send suspension notification for an overdue invoice.
     */
    public function sendSuspensionNotification($id)
    {
        $invoice = Invoice::with(['client', 'project', 'sharedClients', 'emailRecipients'])->findOrFail($id);
        
        // Find associated hosting
        $hosting = ClientHosting::where('project_id', $invoice->project_id)
            ->where('client_id', $invoice->client_id)
            ->first();

        if (!$hosting) {
            return redirect()->back()->with('error', 'No associated hosting found for this invoice.');
        }

        $recipients = $this->invoiceRecipientResolver->emails($invoice);
        if (!empty($recipients)) {
            Mail::to($recipients)->send(new HostingSuspensionMail($invoice, $hosting));
        }

        return redirect()->back()->with('success', 'Suspension notification sent to ' . $invoice->client->name);
    }

    private function syncInvoiceTotals(array &$validated): void
    {
        $itemsTotal = collect($validated['items'] ?? [])->sum(function ($item) {
            $lineTotal = $item['total'] ?? null;
            if ($lineTotal === null || $lineTotal === '') {
                $lineTotal = ((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0));
            }

            return (float) $lineTotal;
        });

        $crTotal = 0;
        if (!empty($validated['selected_crs'])) {
            $crTotal = (float) ChangeRequest::whereIn('id', $validated['selected_crs'])->sum('amount');
        }

        $subTotal = round($itemsTotal + $crTotal, 2);
        if ($subTotal > 0) {
            $tax = (float) ($validated['tax'] ?? 0);
            $validated['sub_total'] = $subTotal;
            $validated['total_amount'] = round($subTotal + $tax, 2);
            return;
        }

        if (!isset($validated['sub_total'])) {
            $validated['sub_total'] = $validated['total_amount'];
        }
    }

    private function canClientAccessInvoice(Invoice $invoice, ?string $clientId): bool
    {
        if (!$clientId) {
            return false;
        }

        if ($invoice->client_id === $clientId) {
            return true;
        }

        return $invoice->sharedClients()->whereKey($clientId)->exists();
    }
}
