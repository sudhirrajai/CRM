<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProjectFileController extends Controller
{
    public function index(Project $project)
    {
        $this->authorizeAccess($project);

        $files = $project->files()->with('user')->latest()->get();

        return response()->json($files);
    }

    public function upload(Request $request, Project $project)
    {
        $this->authorizeAccess($project);

        $maxSize = ($project->max_file_size ?: 10) * 1024; // Convert MB to KB

        $request->validate([
            'file' => "required|file|max:{$maxSize}",
        ]);

        $file = $request->file('file');
        $path = $file->store("projects/{$project->id}/files", 'local');

        $projectFile = $project->files()->create([
            'user_id' => auth()->id(),
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json($projectFile->load('user'));
    }

    public function download(ProjectFile $file)
    {
        $this->authorizeAccess($file->project);

        if (!Storage::disk('local')->exists($file->path)) {
            abort(404, 'File not found on disk.');
        }

        return Storage::disk('local')->download($file->path, $file->name);
    }

    public function destroy(ProjectFile $file)
    {
        $this->authorizeAccess($file->project);

        // Only uploader or admin can delete
        if (auth()->id() !== $file->user_id && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        Storage::disk('local')->delete($file->path);
        $file->delete();

        return response()->json(['message' => 'File deleted successfully.']);
    }

    public function createShareLink(Request $request, ProjectFile $file)
    {
        $this->authorizeAccess($file->project);

        $validated = $request->validate([
            'expires_at' => 'nullable|date|after:now',
        ]);

        $file->update([
            'share_token' => (string) Str::uuid(),
            'shared_at' => Carbon::now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json([
            'share_url' => route('public.files.download', $file->share_token),
            'expires_at' => $file->expires_at ? $file->expires_at->toDateTimeString() : null,
        ]);
    }

    public function revokeShareLink(ProjectFile $file)
    {
        $this->authorizeAccess($file->project);

        $file->update([
            'share_token' => null,
            'shared_at' => null,
            'expires_at' => null,
        ]);

        return response()->json(['message' => 'Share link revoked.']);
    }

    public function publicDownload($token)
    {
        $file = ProjectFile::where('share_token', $token)->firstOrFail();

        if (!$file->isShareValid()) {
            abort(403, 'This share link has expired or is no longer valid.');
        }

        if (!Storage::disk('local')->exists($file->path)) {
            abort(404, 'File not found on disk.');
        }

        return Storage::disk('local')->download($file->path, $file->name);
    }

    protected function authorizeAccess(Project $project)
    {
        $user = auth()->user();
        if (!$user->hasRole(['admin', 'staff']) && $project->client_id !== $user->client_id) {
            abort(403);
        }
    }
}
