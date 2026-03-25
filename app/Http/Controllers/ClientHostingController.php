<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ClientHostingRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientHostingController extends Controller
{
    public function __construct(
        protected ClientHostingRepositoryInterface $hostingRepo
    ) {}

    public function index()
    {
        return Inertia::render('Hostings/Index', [
            'hostings' => $this->hostingRepo->all()->load(['client', 'server', 'currency', 'project'])
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
        ]);

        $this->hostingRepo->create($validated);
        return redirect()->route('hostings.index');
    }

    public function show($id)
    {
        return Inertia::render('Hostings/Show', [
            'hosting' => $this->hostingRepo->find($id)->load(['client', 'server', 'currency', 'project'])
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
