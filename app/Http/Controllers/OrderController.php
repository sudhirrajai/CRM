<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $query = \App\Models\Order::with('client')->latest();

        if ($user->hasRole('client')) {
            $orders = $query->where('client_id', $user->client_id)->get();
        } else {
            $orders = $query->get();
        }

        return \Inertia\Inertia::render('Orders/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return \Inertia\Inertia::render('Orders/Create', [
            'clients' => \App\Models\Client::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'order_number' => 'required|string|unique:orders',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|string',
        ]);

        \App\Models\Order::create($validated);

        return redirect()->route('orders.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = \App\Models\Order::with('client')->findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('client') && $order->client_id !== $user->client_id) {
            abort(403);
        }

        return \Inertia\Inertia::render('Orders/Show', [
            'order' => $order
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
