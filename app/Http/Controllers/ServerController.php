<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ServerRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServerController extends Controller
{
    public function __construct(
        protected ServerRepositoryInterface $serverRepo
    ) {}

    public function index()
    {
        return Inertia::render('Servers/Index', [
            'servers' => $this->serverRepo->all()->load('currency')
        ]);
    }

    public function create()
    {
        return Inertia::render('Servers/Create', [
            'currencies' => \App\Models\Currency::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'monthly_cost' => 'required|numeric',
            'currency_id' => 'required|exists:currencies,id',
            'credentials' => 'nullable|array',
            'status' => 'required|string',
        ]);

        $this->serverRepo->create($validated);
        return redirect()->route('servers.index');
    }

    public function show($id)
    {
        return Inertia::render('Servers/Show', [
            'server' => $this->serverRepo->find($id)
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Servers/Edit', [
            'server' => $this->serverRepo->find($id),
            'currencies' => \App\Models\Currency::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'monthly_cost' => 'required|numeric',
            'currency_id' => 'required|exists:currencies,id',
            'credentials' => 'nullable|array',
            'status' => 'required|string',
        ]);

        $this->serverRepo->update($id, $validated);
        return redirect()->route('servers.index');
    }

    public function destroy($id)
    {
        $this->serverRepo->delete($id);
        return redirect()->route('servers.index');
    }
}
