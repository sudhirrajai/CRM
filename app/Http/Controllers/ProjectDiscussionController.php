<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDiscussion;
use App\Models\ProjectDiscussionAttachment;
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
            ->latest()
            ->paginate(20);

        return response()->json($discussions);
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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB limit
        ]);

        $discussion = $project->discussions()->create([
            'user_id' => $user->id,
            'message' => $validated['message'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
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

        return response()->json($discussion->load(['user', 'attachments', 'replies.user', 'replies.attachments']));
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
        ]);

        $discussion->update([
            'message' => $validated['message'],
            'is_edited' => true,
            'edited_at' => Carbon::now(),
        ]);

        return response()->json($discussion->load(['user', 'attachments', 'replies.user', 'replies.attachments']));
    }

    public function destroy(Project $project, ProjectDiscussion $discussion)
    {
        $user = auth()->user();
        if ($discussion->user_id !== $user->id && !$user->hasRole(['admin'])) {
            abort(403);
        }

        // Delete attachments from storage
        foreach ($discussion->attachments as $attachment) {
            $path = str_replace('/storage/', '', $attachment->file_path);
            Storage::disk('public')->delete($path);
        }

        $discussion->delete();

        return response()->json(['message' => 'Message deleted successfully.']);
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
