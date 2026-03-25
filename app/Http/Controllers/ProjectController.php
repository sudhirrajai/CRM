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
        return Inertia::render('Projects/Index', [
            'projects' => $this->projectRepo->all()->load('client')
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
        ]);

        $this->projectRepo->create($validated);
        return redirect()->route('projects.index');
    }

    public function show($id)
    {
        return Inertia::render('Projects/Show', [
            'project' => $this->projectRepo->find($id)->load('client')
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Projects/Edit', [
            'project' => $this->projectRepo->find($id),
            'clients' => \App\Models\Client::where('status', 'active')->get(),
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
        ]);

        $this->projectRepo->update($id, $validated);
        return redirect()->route('projects.index');
    }

    public function destroy($id)
    {
        $this->projectRepo->delete($id);
        return redirect()->route('projects.index');
    }
}
