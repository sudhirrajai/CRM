<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadGetterGroup;
use App\Models\LeadGetterResult;
use App\Models\LeadGetterTask;
use App\Models\LeadPipelineStage;
use App\Models\Setting;
use App\Models\User;
use App\Jobs\FetchLeadsJob;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadGetterController extends Controller
{
    /**
     * Display groups dashboard.
     */
    public function index()
    {
        $groups = LeadGetterGroup::withCount('tasks')
            ->withCount(['results as total_results_count'])
            ->withCount(['results as qualified_results_count' => function ($q) {
                $q->where('lead_getter_results.status', 'qualified');
            }])
            ->with('user:id,name')
            ->latest()
            ->get();

        return Inertia::render('LeadGetter/Index', [
            'groups' => $groups,
        ]);
    }

    /**
     * Store a new group.
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();

        LeadGetterGroup::create($validated);

        return redirect()->back()->with('success', 'Group created successfully.');
    }

    /**
     * Update a group.
     */
    public function updateGroup(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $group = LeadGetterGroup::findOrFail($id);
        $group->update($validated);

        return redirect()->back()->with('success', 'Group updated successfully.');
    }

    /**
     * Delete a group.
     */
    public function destroyGroup($id)
    {
        $group = LeadGetterGroup::findOrFail($id);
        $group->delete();

        return redirect()->route('lead-getter.index')->with('success', 'Group deleted successfully.');
    }

    /**
     * Show group with tasks.
     */
    public function showGroup($id)
    {
        $group = LeadGetterGroup::with(['user:id,name'])->findOrFail($id);

        $tasks = LeadGetterTask::where('lead_getter_group_id', $id)
            ->withCount('results')
            ->withCount(['results as qualified_count' => function ($q) {
                $q->where('lead_getter_results.status', 'qualified');
            }])
            ->withCount(['results as new_count' => function ($q) {
                $q->where('lead_getter_results.status', 'new');
            }])
            ->with('user:id,name')
            ->latest()
            ->get();

        return Inertia::render('LeadGetter/GroupShow', [
            'group' => $group,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Create a new search task and dispatch the job.
     */
    public function storeTask(Request $request, $groupId)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        // Verify API key is configured
        $apiKey = Setting::getValue('serpapi_key');
        if (empty($apiKey)) {
            return redirect()->back()->with('error', 'SerpApi API key is not configured. Please set it in Settings → Lead Getter.');
        }

        $group = LeadGetterGroup::findOrFail($groupId);

        $task = LeadGetterTask::create([
            'lead_getter_group_id' => $group->id,
            'query' => $validated['query'],
            'location' => $validated['location'],
            'api_provider' => 'serpapi',
            'status' => 'pending',
            'user_id' => auth()->id(),
        ]);

        // Dispatch background job
        FetchLeadsJob::dispatch($task);

        return redirect()->back()->with('success', 'Search task created. Results will appear shortly.');
    }

    /**
     * Show task results.
     */
    public function showTask($taskId)
    {
        $task = LeadGetterTask::with(['group', 'user:id,name'])->findOrFail($taskId);

        $results = LeadGetterResult::where('lead_getter_task_id', $taskId)
            ->with('lead:id,title,lead_pipeline_stage_id')
            ->orderByRaw("CASE WHEN status = 'new' THEN 0 WHEN status = 'qualified' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        $users = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'staff']))->get();
        $stages = LeadPipelineStage::ordered()->get();

        return Inertia::render('LeadGetter/TaskResults', [
            'task' => $task,
            'results' => $results,
            'users' => $users,
            'stages' => $stages,
        ]);
    }

    /**
     * Qualify a result — create a CRM Lead from this result.
     */
    public function qualifyResult(Request $request, $resultId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'lead_pipeline_stage_id' => 'required|exists:lead_pipeline_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'value' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|in:website,referral,social_media,cold_call,email,advertisement,other',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'description' => 'nullable|string',
        ]);

        $result = LeadGetterResult::findOrFail($resultId);

        if ($result->status === 'qualified') {
            return redirect()->back()->with('error', 'This result has already been qualified.');
        }

        // Set position to end of stage
        $maxPosition = Lead::where('lead_pipeline_stage_id', $validated['lead_pipeline_stage_id'])->max('position') ?? -1;

        // Create CRM Lead
        $lead = Lead::create([
            'title' => $validated['title'],
            'company' => $validated['company'],
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
            'lead_pipeline_stage_id' => $validated['lead_pipeline_stage_id'],
            'assigned_to' => $validated['assigned_to'],
            'value' => $validated['value'] ?? 0,
            'source' => $validated['source'] ?? 'other',
            'priority' => $validated['priority'] ?? 'medium',
            'description' => $validated['description'],
            'position' => $maxPosition + 1,
        ]);

        // Log creation activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'description' => 'Lead created via Lead Getter (qualified from search results)',
        ]);

        // Update result status
        $result->update([
            'status' => 'qualified',
            'qualified_at' => now(),
            'lead_id' => $lead->id,
        ]);

        return redirect()->back()->with('success', "Lead \"{$lead->title}\" created and added to pipeline.");
    }

    /**
     * Bulk qualify multiple results.
     */
    public function bulkQualify(Request $request)
    {
        $validated = $request->validate([
            'result_ids' => 'required|array|min:1',
            'result_ids.*' => 'exists:lead_getter_results,id',
            'lead_pipeline_stage_id' => 'required|exists:lead_pipeline_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'source' => 'nullable|string|in:website,referral,social_media,cold_call,email,advertisement,other',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
        ]);

        $results = LeadGetterResult::whereIn('id', $validated['result_ids'])
            ->where('status', 'new')
            ->get();

        $count = 0;
        $maxPosition = Lead::where('lead_pipeline_stage_id', $validated['lead_pipeline_stage_id'])->max('position') ?? -1;

        foreach ($results as $result) {
            $maxPosition++;

            $lead = Lead::create([
                'title' => $result->title,
                'company' => $result->company,
                'contact_name' => $result->contact_name,
                'contact_email' => $result->email,
                'contact_phone' => $result->phone,
                'lead_pipeline_stage_id' => $validated['lead_pipeline_stage_id'],
                'assigned_to' => $validated['assigned_to'],
                'value' => 0,
                'source' => $validated['source'] ?? 'other',
                'priority' => $validated['priority'] ?? 'medium',
                'description' => "Website: {$result->website}\nAddress: {$result->address}\nRating: {$result->rating} ({$result->reviews_count} reviews)",
                'position' => $maxPosition,
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => 'note',
                'description' => 'Lead created via Lead Getter (bulk qualified)',
            ]);

            $result->update([
                'status' => 'qualified',
                'qualified_at' => now(),
                'lead_id' => $lead->id,
            ]);

            $count++;
        }

        return redirect()->back()->with('success', "{$count} leads qualified and added to pipeline.");
    }

    /**
     * Disqualify a result.
     */
    public function disqualifyResult($resultId)
    {
        $result = LeadGetterResult::findOrFail($resultId);

        if ($result->status === 'qualified') {
            return redirect()->back()->with('error', 'Cannot disqualify an already qualified result.');
        }

        $result->update(['status' => 'disqualified']);

        return redirect()->back()->with('success', 'Result disqualified.');
    }

    /**
     * Export task results as CSV.
     */
    public function exportResults($taskId)
    {
        $task = LeadGetterTask::findOrFail($taskId);
        $results = LeadGetterResult::where('lead_getter_task_id', $taskId)->get();

        $csv = "Business Name,Company,Phone,Email,Website,Address,Rating,Reviews,Category,Status\n";

        foreach ($results as $result) {
            $csv .= '"' . str_replace('"', '""', $result->title) . '",';
            $csv .= '"' . str_replace('"', '""', $result->company ?? '') . '",';
            $csv .= '"' . ($result->phone ?? '') . '",';
            $csv .= '"' . ($result->email ?? '') . '",';
            $csv .= '"' . ($result->website ?? '') . '",';
            $csv .= '"' . str_replace('"', '""', $result->address ?? '') . '",';
            $csv .= '"' . ($result->rating ?? '') . '",';
            $csv .= '"' . ($result->reviews_count ?? '') . '",';
            $csv .= '"' . str_replace('"', '""', $result->category ?? '') . '",';
            $csv .= '"' . $result->status . '"' . "\n";
        }

        $filename = 'leads_' . str_replace(' ', '_', strtolower($task->query)) . '_' . date('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
