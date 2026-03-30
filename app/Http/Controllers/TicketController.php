<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Project;
use App\Models\User;
use App\Mail\TicketCreatedMail;
use App\Mail\TicketAssignedMail;
use App\Mail\TicketReplyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Ticket::with(['client', 'project', 'user', 'assignee']);

        if ($user->client_id) {
            $query->where('client_id', $user->client_id);
        }

        return Inertia::render('Tickets/Index', [
            'tickets' => $query->latest()->get(),
            'canManage' => $user->hasRole(['admin', 'staff']),
            'users' => User::role(['admin', 'staff'])->get(),
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $projects = [];

        if ($user->client_id) {
            $projects = Project::where('client_id', $user->client_id)->get();
        }

        return Inertia::render('Tickets/Create', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'client_id' => $user->client_id,
            'project_id' => $validated['project_id'],
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        // Notify Admins
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new TicketCreatedMail($ticket));
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $user = Auth::user();
        
        if ($user->client_id && $ticket->client_id !== $user->client_id) {
            abort(403);
        }

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket->load(['client', 'project', 'user', 'assignee', 'messages.user']),
            'users' => User::role(['admin', 'staff'])->get(),
            'canManage' => $user->hasRole(['admin', 'staff']),
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        // If it's not closed, update status if staff replies
        if ($ticket->status === 'open' && $user->hasRole(['admin', 'staff'])) {
            $ticket->update(['status' => 'in-progress']);
        }

        // Notify other side
        $recipientEmail = null;
        if ($user->id === $ticket->user_id) {
            // Client replied, notify assignee or admins
            if ($ticket->assigned_to) {
                $recipientEmail = $ticket->assignee->email;
            } else {
                // To admins if not assigned
                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new TicketReplyMail($ticket, $message));
                }
            }
        } else {
            // Admin/Staff replied, notify client
            $recipientEmail = $ticket->user->email;
        }

        if ($recipientEmail) {
            Mail::to($recipientEmail)->send(new TicketReplyMail($ticket, $message));
        }

        return back()->with('success', 'Reply sent.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        if (!Auth::user()->hasRole(['admin', 'staff'])) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $ticket->update(['assigned_to' => $validated['user_id']]);

        $assignee = User::find($validated['user_id']);
        Mail::to($assignee->email)->send(new TicketAssignedMail($ticket));

        return back()->with('success', 'Ticket assigned successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        if (!Auth::user()->hasRole(['admin', 'staff'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in-progress,closed,pending',
        ]);

        $ticket->update(['status' => $validated['status']]);

        return back()->with('success', 'Ticket status updated.');
    }
}
