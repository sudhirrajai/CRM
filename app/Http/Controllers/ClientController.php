<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ClientRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepo
    ) {}

    public function index()
    {
        return Inertia::render('Clients/Index', [
            'clients' => $this->clientRepo->getWithRelations()
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create', [
            'currencies' => \App\Models\Currency::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'required|string',
        ]);

        $this->clientRepo->create($validated);
        return redirect()->route('clients.index');
    }

    public function show($id)
    {
        return Inertia::render('Clients/Show', [
            'client' => $this->clientRepo->find($id)
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Clients/Edit', [
            'client' => $this->clientRepo->find($id),
            'currencies' => \App\Models\Currency::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'required|string',
        ]);

        $this->clientRepo->update($id, $validated);
        return redirect()->route('clients.index');
    }

    public function destroy($id)
    {
        $this->clientRepo->delete($id);
        return redirect()->route('clients.index');
    }
}
