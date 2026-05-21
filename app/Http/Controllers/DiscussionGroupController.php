<?php

namespace App\Http\Controllers;

use App\Models\DiscussionGroup;
use App\Models\ProjectDiscussion;
use App\Models\ProjectDiscussionAttachment;
use App\Models\DiscussionRead;
use App\Models\User;
use App\Events\NewDiscussionMessage;
use App\Events\DiscussionMessageUpdated;
use App\Events\DiscussionMessageDeleted;
use App\Events\UserReadDiscussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DiscussionGroupController extends Controller
{
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only Admins can create groups.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group = DiscussionGroup::create([
            'name' => $validated['name'],
            'created_by' => auth()->id(),
        ]);

        $userIds = collect($validated['user_ids'] ?? []);
        // Creator should always be a member
        $userIds->push(auth()->id());

        $group->members()->sync($userIds->unique());

        return response()->json([
            'message' => 'Group created successfully.',
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'is_group' => true,
                'members_count' => $group->members()->count(),
            ]
        ]);
    }

    public function update(Request $request, DiscussionGroup $group)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only Admins can update groups.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group->update([
            'name' => $validated['name']
        ]);

        if ($request->has('user_ids')) {
            $userIds = collect($validated['user_ids']);
            // Keep creator if desired, or let admin fully customize. Let's make sure creator remains or at least admin gets what they selected.
            $group->members()->sync($userIds->unique());
        }

        return response()->json([
            'message' => 'Group updated successfully.',
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'is_group' => true,
                'members_count' => $group->members()->count(),
            ]
        ]);
    }

    public function destroy(DiscussionGroup $group)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only Admins can delete groups.');
        }

        $group->delete();

        return response()->json(['message' => 'Group deleted successfully.']);
    }

    public function index(DiscussionGroup $group)
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$group->members()->where('users.id', $user->id)->exists()) {
            abort(403, 'You are not a member of this group.');
        }

        $discussions = $group->discussions()
            ->with(['user', 'attachments', 'replies.user', 'replies.attachments'])
            ->oldest()
            ->paginate(100);

        // Get read status for the user
        $readStatus = DiscussionRead::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->first();

        // Map group members to the standard format required by frontend
        $projectMembers = $group->members->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avatar_initial' => strtoupper(substr($u->name, 0, 1)),
                'role' => $u->getRoleNames()->first() ?? 'Client',
            ];
        });

        return response()->json([
            'discussions' => $discussions,
            'read_status' => $readStatus,
            'project_members' => $projectMembers,
        ]);
    }

    public function storeMessage(Request $request, DiscussionGroup $group)
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$group->members()->where('users.id', $user->id)->exists()) {
            abort(403, 'You are not a member of this group.');
        }

        $validated = $request->validate([
            'message' => 'required_without:attachments|nullable|string',
            'parent_id' => 'nullable|exists:project_discussions,id',
            'mentions' => 'nullable|array',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB
        ]);

        $discussion = $group->discussions()->create([
            'user_id' => $user->id,
            'message' => $validated['message'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'mentions' => $validated['mentions'] ?? [],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('group-discussions/' . $group->id, 'public');
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

        return response()->json($discussion);
    }

    public function updateMessage(Request $request, DiscussionGroup $group, ProjectDiscussion $discussion)
    {
        $user = auth()->user();
        if ($discussion->user_id !== $user->id) {
            abort(403);
        }

        // 10 minutes edit window
        if (abs(Carbon::now()->diffInMinutes($discussion->created_at)) > 10) {
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

    public function destroyMessage(DiscussionGroup $group, ProjectDiscussion $discussion)
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
        broadcast(new DiscussionMessageDeleted(null, $messageId, $parentId, $group->id))->toOthers();

        return response()->json(['message' => 'Message deleted successfully.']);
    }

    public function markAsRead(Request $request, DiscussionGroup $group)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'last_read_message_id' => 'required|exists:project_discussions,id',
        ]);

        DiscussionRead::updateOrCreate(
            ['group_id' => $group->id, 'user_id' => $user->id],
            [
                'last_read_message_id' => $validated['last_read_message_id'],
                'last_read_at' => now()
            ]
        );

        // Broadcast the read event
        broadcast(new UserReadDiscussion(null, $user->id, $user->name, $validated['last_read_message_id'], $group->id))->toOthers();

        return response()->json(['success' => true]);
    }

    public function availableMembers(DiscussionGroup $group)
    {
        if (!auth()->user()->hasRole('admin')) abort(403);

        $assignedIds = $group->members()->pluck('users.id');
        return User::whereNotIn('id', $assignedIds)->get()->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->getRoleNames()->first() ?? 'Client',
            ];
        });
    }

    public function assignMember(Request $request, DiscussionGroup $group)
    {
        if (!auth()->user()->hasRole('admin')) abort(403);

        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        $group->members()->syncWithoutDetaching([$validated['user_id']]);

        return response()->json(['message' => 'Member added successfully.']);
    }

    public function unassignMember(DiscussionGroup $group, User $user)
    {
        if (!auth()->user()->hasRole('admin')) abort(403);

        $group->members()->detach($user->id);
        return response()->json(['message' => 'Member removed successfully.']);
    }

    public function allUsers()
    {
        if (!auth()->user()->hasRole('admin')) abort(403);

        return User::all()->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->getRoleNames()->first() ?? 'Client',
            ];
        });
    }

    public function downloadAttachment(DiscussionGroup $group, ProjectDiscussionAttachment $attachment)
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$group->members()->where('users.id', $user->id)->exists()) {
            abort(403, 'You are not a member of this group.');
        }

        $path = str_replace('/storage/', '', $attachment->file_path);
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path, $attachment->file_name);
    }
}
