<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketAttachment;
use App\Models\Project;
use App\Models\User;
use App\Mail\TicketCreatedMail;
use App\Mail\TicketAssignedMail;
use App\Mail\TicketReplyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $ticket = Ticket::create([
            'client_id' => $user->client_id,
            'project_id' => $validated['project_id'],
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        $messageRecord = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets', 'public');
                TicketAttachment::create([
                    'ticket_message_id' => $messageRecord->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

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
            'ticket' => $ticket->load(['client', 'project', 'user', 'assignee', 'messages.user', 'messages.attachments']),
            'users' => User::role(['admin', 'staff'])->get(),
            'canManage' => $user->hasRole(['admin', 'staff']),
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets', 'public');
                TicketAttachment::create([
                    'ticket_message_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

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

    public function downloadAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        if ($attachment->message->ticket_id !== $ticket->id) {
            abort(404);
        }

        $user = Auth::user();
        if ($user->client_id && $ticket->client_id !== $user->client_id) {
            abort(403);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
