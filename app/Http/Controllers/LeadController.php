<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadPipelineStage;
use App\Models\Client;
use App\Models\Currency;
use App\Models\User;
use App\Repositories\Interfaces\LeadRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function __construct(
        protected LeadRepositoryInterface $leadRepo
    ) {}

    public function index(Request $request)
    {
        $stages = LeadPipelineStage::ordered()->withCount('leads')->get();
        $grouped = $this->leadRepo->getGroupedByStage();
        $allLeads = $this->leadRepo->getWithRelations();
        $users = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'staff']))->get();

        return Inertia::render('Leads/Index', [
            'stages' => $stages,
            'groupedLeads' => $grouped,
            'allLeads' => $allLeads,
            'users' => $users,
            'stats' => [
                'total' => $allLeads->count(),
                'total_value' => $this->leadRepo->getTotalValue(),
                'conversion_rate' => $this->leadRepo->getConversionRate(),
                'won' => $allLeads->whereNotNull('won_at')->count(),
                'lost' => $allLeads->whereNotNull('lost_at')->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Leads/Create', [
            'stages' => LeadPipelineStage::ordered()->get(),
            'currencies' => Currency::all(),
            'users' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'staff']))->get(),
            'clients' => Client::orderBy('name')->get(['id', 'name', 'email', 'company']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'lead_pipeline_stage_id' => 'required|exists:lead_pipeline_stages,id',
            'client_id' => 'nullable|exists:clients,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'source' => 'required|string|in:website,referral,social_media,cold_call,email,advertisement,other',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'expected_close_date' => 'nullable|date',
            'description' => 'nullable|string',
            'auto_convert' => 'nullable|boolean',
        ]);

        // Set position to end of stage
        $maxPosition = Lead::where('lead_pipeline_stage_id', $validated['lead_pipeline_stage_id'])->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        $lead = $this->leadRepo->create($validated);

        // Log creation activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'description' => 'Lead created',
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show($id)
    {
        $lead = Lead::with(['stage', 'client', 'currency', 'assignedUser', 'convertedClient', 'activities.user'])->findOrFail($id);
        $stages = LeadPipelineStage::ordered()->get();

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
            'stages' => $stages,
        ]);
    }

    public function edit($id)
    {
        $lead = Lead::with(['stage', 'client', 'currency', 'assignedUser'])->findOrFail($id);

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'stages' => LeadPipelineStage::ordered()->get(),
            'currencies' => Currency::all(),
            'users' => User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'staff']))->get(),
            'clients' => Client::orderBy('name')->get(['id', 'name', 'email', 'company']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'lead_pipeline_stage_id' => 'required|exists:lead_pipeline_stages,id',
            'client_id' => 'nullable|exists:clients,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'source' => 'required|string|in:website,referral,social_media,cold_call,email,advertisement,other',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'expected_close_date' => 'nullable|date',
            'description' => 'nullable|string',
            'auto_convert' => 'nullable|boolean',
            'lost_reason' => 'nullable|string|max:500',
        ]);

        $lead = $this->leadRepo->find($id);
        $oldStageId = $lead->lead_pipeline_stage_id;

        $this->leadRepo->update($id, $validated);

        // Log stage change if different
        if ($oldStageId !== $validated['lead_pipeline_stage_id']) {
            $oldStage = LeadPipelineStage::find($oldStageId);
            $newStage = LeadPipelineStage::find($validated['lead_pipeline_stage_id']);
            LeadActivity::create([
                'lead_id' => $id,
                'user_id' => auth()->id(),
                'type' => 'stage_change',
                'description' => "Stage changed from \"{$oldStage->name}\" to \"{$newStage->name}\"",
                'metadata' => ['from_stage' => $oldStage->name, 'to_stage' => $newStage->name],
            ]);
        }

        return redirect()->route('leads.show', $id)->with('success', 'Lead updated successfully.');
    }

    public function destroy($id)
    {
        $this->leadRepo->delete($id);
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    /**
     * Update lead stage via drag-and-drop (AJAX).
     */
    public function updateStage(Request $request, $id)
    {
        $validated = $request->validate([
            'lead_pipeline_stage_id' => 'required|exists:lead_pipeline_stages,id',
            'position' => 'required|integer|min:0',
        ]);

        $lead = $this->leadRepo->find($id);
        $oldStageId = $lead->lead_pipeline_stage_id;
        $oldStage = LeadPipelineStage::find($oldStageId);
        $newStage = LeadPipelineStage::find($validated['lead_pipeline_stage_id']);

        $updatedLead = $this->leadRepo->updateStage($id, $validated['lead_pipeline_stage_id'], $validated['position']);

        // Log stage change
        if ($oldStageId !== $validated['lead_pipeline_stage_id']) {
            LeadActivity::create([
                'lead_id' => $id,
                'user_id' => auth()->id(),
                'type' => 'stage_change',
                'description' => "Stage changed from \"{$oldStage->name}\" to \"{$newStage->name}\"",
                'metadata' => ['from_stage' => $oldStage->name, 'to_stage' => $newStage->name],
            ]);

            // Auto-convert if won stage and auto_convert is enabled
            if ($newStage->is_won && $updatedLead->auto_convert && !$updatedLead->converted_client_id) {
                $this->performConversion($updatedLead);
            }
        }

        return response()->json(['lead' => $updatedLead, 'message' => 'Stage updated']);
    }

    /**
     * Convert a won lead to a client.
     */
    public function convert($id)
    {
        $lead = $this->leadRepo->find($id);

        if ($lead->converted_client_id) {
            return redirect()->back()->with('error', 'This lead has already been converted.');
        }

        $client = $this->performConversion($lead);

        return redirect()->route('leads.show', $id)->with('success', "Lead converted to client: {$client->name}");
    }

    /**
     * Add an activity to a lead.
     */
    public function addActivity(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:note,call,email,meeting,task',
            'description' => 'required|string|max:2000',
        ]);

        $activity = LeadActivity::create([
            'lead_id' => $id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'description' => $validated['description'],
        ]);

        return redirect()->back()->with('success', 'Activity logged successfully.');
    }

    /**
     * Export leads as CSV.
     */
    public function export(Request $request)
    {
        $leads = Lead::with(['stage', 'currency', 'assignedUser'])->get();

        $csv = "Title,Company,Contact Name,Contact Email,Contact Phone,Stage,Value,Currency,Source,Priority,Assigned To,Expected Close Date,Status,Created At\n";
        foreach ($leads as $lead) {
            $status = $lead->won_at ? 'Won' : ($lead->lost_at ? 'Lost' : 'Active');
            $csv .= '"' . str_replace('"', '""', $lead->title) . '",';
            $csv .= '"' . str_replace('"', '""', $lead->company ?? '') . '",';
            $csv .= '"' . str_replace('"', '""', $lead->contact_name ?? '') . '",';
            $csv .= '"' . ($lead->contact_email ?? '') . '",';
            $csv .= '"' . ($lead->contact_phone ?? '') . '",';
            $csv .= '"' . ($lead->stage->name ?? '') . '",';
            $csv .= '"' . ($lead->value ?? '0') . '",';
            $csv .= '"' . ($lead->currency->code ?? '') . '",';
            $csv .= '"' . $lead->source . '",';
            $csv .= '"' . $lead->priority . '",';
            $csv .= '"' . ($lead->assignedUser->name ?? '') . '",';
            $csv .= '"' . ($lead->expected_close_date?->format('Y-m-d') ?? '') . '",';
            $csv .= '"' . $status . '",';
            $csv .= '"' . $lead->created_at->format('Y-m-d H:i') . '"' . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads_export_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Import leads from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // skip header row

        $defaultStage = LeadPipelineStage::where('is_default', true)->first()
            ?? LeadPipelineStage::ordered()->first();

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            try {
                $maxPosition = Lead::where('lead_pipeline_stage_id', $defaultStage->id)->max('position') ?? -1;

                Lead::create([
                    'title' => $row[0] ?? 'Untitled Lead',
                    'company' => $row[1] ?? null,
                    'contact_name' => $row[2] ?? null,
                    'contact_email' => $row[3] ?? null,
                    'contact_phone' => $row[4] ?? null,
                    'lead_pipeline_stage_id' => $defaultStage->id,
                    'value' => is_numeric($row[6] ?? null) ? $row[6] : null,
                    'source' => in_array($row[8] ?? '', ['website','referral','social_media','cold_call','email','advertisement','other']) ? $row[8] : 'other',
                    'priority' => in_array($row[9] ?? '', ['low','medium','high','urgent']) ? $row[9] : 'medium',
                    'position' => $maxPosition + 1,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + count($errors) + 2) . ": " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "{$imported} leads imported successfully.";
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " rows had errors.";
        }

        return redirect()->route('leads.index')->with('success', $message);
    }

    /**
     * Perform the actual lead-to-client conversion.
     */
    protected function performConversion(Lead $lead): Client
    {
        $client = Client::create([
            'name' => $lead->contact_name ?? $lead->title,
            'email' => $lead->contact_email ?? '',
            'phone' => $lead->contact_phone,
            'company' => $lead->company,
            'currency_id' => $lead->currency_id ?? Currency::first()?->id,
            'status' => 'active',
        ]);

        $lead->update(['converted_client_id' => $client->id]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'description' => "Lead converted to client: {$client->name}",
            'metadata' => ['converted_client_id' => $client->id],
        ]);

        return $client;
    }
}
