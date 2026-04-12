<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ProjectRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectRepositoryInterface $projectRepo
    ) {}

    public function index()
    {
        $user = auth()->user();
        $query = $this->projectRepo->all();

        if (!$user->hasRole(['admin', 'staff'])) {
            $projects = $this->projectRepo->getByClient($user->client_id)->load('client');
        } else {
            $projects = $query->load('client');
        }

        return Inertia::render('Projects/Index', [
            'projects' => $projects
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Create', [
            'clients' => \App\Models\Client::where('status', 'active')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'tech_stack' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'priority' => 'required|string|in:low,medium,high',
            'max_file_size' => 'nullable|integer|min:1',
        ]);

        $this->projectRepo->create($validated);
        return redirect()->route('projects.index');
    }

    public function show($id)
    {
        $project = $this->projectRepo->find($id)->load(['client.currency', 'invoices.currency', 'milestones', 'members', 'changeRequests']);
        $user = auth()->user();

        if (!$user->hasRole(['admin', 'staff']) && $project->client_id !== $user->client_id) {
            abort(403);
        }

        return Inertia::render('Projects/Show', [
            'project' => $project
        ]);
    }

    public function edit($id)
    {
        $project = $this->projectRepo->find($id)->load(['milestones', 'members']);
        
        // Format dates for the HTML5 date input
        if ($project->start_date) {
            $project->start_date = $project->start_date->format('Y-m-d');
        }
        if ($project->end_date) {
            $project->end_date = $project->end_date->format('Y-m-d');
        }

        // Format milestone dates
        foreach ($project->milestones as $milestone) {
            if ($milestone->start_date) {
                $milestone->start_date = $milestone->start_date->format('Y-m-d');
            }
            if ($milestone->end_date) {
                $milestone->end_date = $milestone->end_date->format('Y-m-d');
            }
        }

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'clients' => \App\Models\Client::where('status', 'active')->get(),
            'users' => \App\Models\User::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'tech_stack' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'priority' => 'required|string|in:low,medium,high',
            'max_file_size' => 'nullable|integer|min:1',
            'milestones' => 'nullable|array',
            'milestones.*.id' => 'nullable|string',
            'milestones.*.name' => 'required|string|max:255',
            'milestones.*.start_date' => 'required|date',
            'milestones.*.end_date' => 'required|date',
            'milestones.*.hours' => 'nullable|numeric',
            'milestones.*.description' => 'nullable|string',
        ]);

        $project = $this->projectRepo->update($id, array_diff_key($validated, ['milestones' => '']));

        // Handle milestones
        if (isset($validated['milestones'])) {
            $projectModel = \App\Models\Project::find($id);
            $existingIds = $projectModel->milestones()->pluck('id')->toArray();
            $newIds = [];

            foreach ($validated['milestones'] as $milestoneData) {
                if (isset($milestoneData['id'])) {
                    $milestone = \App\Models\Milestone::find($milestoneData['id']);
                    if ($milestone) {
                        $milestone->update($milestoneData);
                        $newIds[] = $milestone->id;
                    }
                } else {
                    $milestone = $projectModel->milestones()->create($milestoneData);
                    $newIds[] = $milestone->id;
                }
            }

            // Delete removed milestones
            $toDelete = array_diff($existingIds, $newIds);
            \App\Models\Milestone::whereIn('id', $toDelete)->delete();
        }

        // Handle members
        if ($request->has('members')) {
            $projectModel = \App\Models\Project::find($id);
            $syncData = [];
            foreach ($request->input('members') as $member) {
                if (isset($member['id'])) {
                    $syncData[$member['id']] = [
                        'send_invoice' => $member['send_invoice'] ?? false,
                        'assigned_at' => $member['assigned_at'] ?? now()
                    ];
                }
            }
            $projectModel->members()->sync($syncData);
        }

        return redirect()->route('projects.index');
    }

    public function destroy($id)
    {
        $this->projectRepo->delete($id);
        return redirect()->route('projects.index');
    }
}
