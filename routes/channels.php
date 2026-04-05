<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Project Discussion Channel
 * Private channel: authorized for admin/staff OR users belonging to the project's client.
 * Presence channel: same authorization, returns user info for online status.
 */
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    $project = \App\Models\Project::find($projectId);
    if (!$project) return false;

    // Admin always authorized
    if ($user->hasRole('admin')) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    // Staff must be assigned
    if ($user->hasRole('staff')) {
        if ($user->projects()->where('project_id', $projectId)->exists()) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    }

    // Client users belonging to the project's client
    if ($user->client_id && $project->client_id === $user->client_id) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
});
