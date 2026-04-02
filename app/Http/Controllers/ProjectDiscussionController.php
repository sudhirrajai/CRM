<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDiscussion;
use App\Models\ProjectDiscussionAttachment;
use App\Models\DiscussionRead;
use App\Models\User;
use App\Events\NewDiscussionMessage;
use App\Events\DiscussionMessageUpdated;
use App\Events\DiscussionMessageDeleted;
use App\Events\UserReadDiscussion;
use App\Jobs\SendMentionReminderJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProjectDiscussionController extends Controller
{
    public function index(Project $project)
    {
        $user = auth()->user();
        if (!$user->hasRole(['admin', 'staff']) && $project->client_id !== $user->client_id) {
            abort(403);
        }

        $discussions = $project->discussions()
            ->with(['user', 'attachments', 'replies.user', 'replies.attachments'])
            ->oldest()
            ->paginate(100);

        // Get read status for the user
        $readStatus = DiscussionRead::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'discussions' => $discussions,
            'read_status' => $readStatus,
            'project_members' => $this->getProjectMembers($project),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $user = auth()->user();
        if (!$user->hasRole(['admin', 'staff']) && $project->client_id !== $user->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required_without:attachments|nullable|string',
            'parent_id' => 'nullable|exists:project_discussions,id',
            'mentions' => 'nullable|array',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB limit
        ]);

        $discussion = $project->discussions()->create([
            'user_id' => $user->id,
            'message' => $validated['message'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'mentions' => $validated['mentions'] ?? [],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('project-discussions/' . $project->id, 'public');
                $discussion->attachments()->create([
                    'file_path' => Storage::url($path),
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $discussion->load(['user', 'attachments', 'replies.user', 'replies.attachments']);

        // Broadcast the new message
        broadcast(new NewDiscussionMessage($discussion))->toOthers();

        // Queue mention reminders
        if (!empty($discussion->mentions)) {
            foreach ($discussion->mentions as $mentionedUserId) {
                // Don't remind yourself
                if ($mentionedUserId !== $user->id) {
                    SendMentionReminderJob::dispatch($discussion, $mentionedUserId)->delay(now()->addMinutes(30));
                }
            }
        }

        return response()->json($discussion);
    }

    public function update(Request $request, Project $project, ProjectDiscussion $discussion)
    {
        $user = auth()->user();
        if ($discussion->user_id !== $user->id) {
            abort(403);
        }

        // 10 minutes edit window
        if (Carbon::now()->diffInMinutes($discussion->created_at) > 10) {
            return response()->json(['message' => 'Edit time window (10 mins) has expired.'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'mentions' => 'nullable|array',
        ]);

        $discussion->update([
            'message' => $validated['message'],
            'mentions' => $validated['mentions'] ?? $discussion->mentions,
            'is_edited' => true,
            'edited_at' => Carbon::now(),
        ]);

        $discussion->load(['user', 'attachments', 'replies.user', 'replies.attachments']);

        // Broadcast the update
        broadcast(new DiscussionMessageUpdated($discussion))->toOthers();

        return response()->json($discussion);
    }

    public function destroy(Project $project, ProjectDiscussion $discussion)
    {
        $user = auth()->user();
        if ($discussion->user_id !== $user->id && !$user->hasRole(['admin'])) {
            abort(403);
        }

        $messageId = $discussion->id;
        $parentId = $discussion->parent_id;

        // Delete attachments from storage
        foreach ($discussion->attachments as $attachment) {
            $path = str_replace('/storage/', '', $attachment->file_path);
            Storage::disk('public')->delete($path);
        }

        $discussion->delete();

        // Broadcast deletion
        broadcast(new DiscussionMessageDeleted($project->id, $messageId, $parentId))->toOthers();

        return response()->json(['message' => 'Message deleted successfully.']);
    }

    public function markAsRead(Request $request, Project $project)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'last_read_message_id' => 'required|exists:project_discussions,id',
        ]);

        $read = DiscussionRead::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $user->id],
            [
                'last_read_message_id' => $validated['last_read_message_id'],
                'last_read_at' => now()
            ]
        );

        // Broadcast the read event
        broadcast(new UserReadDiscussion($project->id, $user->id, $user->name, $validated['last_read_message_id']))->toOthers();

        return response()->json(['success' => true]);
    }

    public function projectMembers(Project $project)
    {
        return response()->json($this->getProjectMembers($project));
    }

    protected function getProjectMembers(Project $project)
    {
        // Admins and Staff
        $staff = User::role(['admin', 'staff'])->get();
        
        // Client Users
        $clientUsers = User::where('client_id', $project->client_id)->get();

        return $staff->concat($clientUsers)->unique('id')->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_initial' => strtoupper(substr($user->name, 0, 1)),
                'role' => $user->getRoleNames()->first(),
            ];
        });
    }

    public function downloadAttachment(Project $project, ProjectDiscussionAttachment $attachment)
    {
        $user = auth()->user();
        if (!$user->hasRole(['admin', 'staff']) && $project->client_id !== $user->client_id) {
            abort(403);
        }

        $path = str_replace('/storage/', '', $attachment->file_path);
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path, $attachment->file_name);
    }
}
