<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ChangeRequestController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,invoiced,paid',
        ]);

        $project->changeRequests()->create($validated);

        return redirect()->back()->with('success', 'Change Request added successfully.');
    }

    public function update(Request $request, ChangeRequest $changeRequest)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,invoiced,paid',
        ]);

        $changeRequest->update($validated);

        return redirect()->back()->with('success', 'Change Request updated successfully.');
    }

    public function destroy(ChangeRequest $changeRequest)
    {
        $changeRequest->delete();

        return redirect()->back()->with('success', 'Change Request deleted successfully.');
    }
}
