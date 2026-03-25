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
            'password' => 'nullable|string|min:8',
            'send_credentials' => 'nullable|boolean',
        ]);

        $clientData = \Illuminate\Support\Arr::except($validated, ['password', 'send_credentials']);
        $client = $this->clientRepo->create($clientData);

        if (!empty($validated['password'])) {
            $user = \App\Models\User::updateOrCreate(
                ['email' => $client->email],
                [
                    'name' => $client->name,
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                    'client_id' => $client->id,
                ]
            );
            
            if (\Spatie\Permission\Models\Role::where('name', 'client')->exists()) {
                $user->assignRole('client');
            }

            if (!empty($validated['send_credentials'])) {
                \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\ClientCredentialsMail($client, $validated['password']));
            }
        }

        return redirect()->route('clients.index');
    }

    public function show($id)
    {
        $client = $this->clientRepo->find($id);
        $client->load(['projects', 'invoices.items', 'hostings', 'currency']);
        
        return Inertia::render('Clients/Show', [
            'client' => $client
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
            'password' => 'nullable|string|min:8',
            'send_credentials' => 'nullable|boolean',
        ]);

        $clientData = \Illuminate\Support\Arr::except($validated, ['password', 'send_credentials']);
        $client = $this->clientRepo->update($id, $clientData);

        $user = \App\Models\User::where('client_id', $client->id)->first();
        
        $userData = [
            'name' => $client->name,
            'email' => $client->email,
        ];
        
        if (!empty($validated['password'])) {
            $userData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }
        
        if ($user) {
            $user->update($userData);
        } elseif (!empty($validated['password'])) {
            $userData['client_id'] = $client->id;
            $user = \App\Models\User::create($userData);
            if (\Spatie\Permission\Models\Role::where('name', 'client')->exists()) {
                $user->assignRole('client');
            }
        }

        if (!empty($validated['password']) && !empty($validated['send_credentials'])) {
            \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\ClientCredentialsMail($client, $validated['password']));
        }

        return redirect()->route('clients.index');
    }

    public function destroy($id)
    {
        $this->clientRepo->delete($id);
        return redirect()->route('clients.index');
    }
}
