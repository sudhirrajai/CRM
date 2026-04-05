<?php

namespace App\Http\Controllers;

use App\Models\LeadPipelineStage;
use Illuminate\Http\Request;

class LeadPipelineStageController extends Controller
{
    /**
     * Get all pipeline stages (for AJAX/settings).
     */
    public function index()
    {
        return response()->json([
            'stages' => LeadPipelineStage::ordered()->withCount('leads')->get(),
        ]);
    }

    /**
     * Create a new pipeline stage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
        ]);

        $maxPosition = LeadPipelineStage::max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        // If marking as won, clear other won stages
        if (!empty($validated['is_won'])) {
            LeadPipelineStage::where('is_won', true)->update(['is_won' => false]);
        }
        if (!empty($validated['is_lost'])) {
            LeadPipelineStage::where('is_lost', true)->update(['is_lost' => false]);
        }

        $stage = LeadPipelineStage::create($validated);

        return redirect()->back()->with('success', "Pipeline stage \"{$stage->name}\" created.");
    }

    /**
     * Update an existing pipeline stage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
        ]);

        $stage = LeadPipelineStage::findOrFail($id);

        // If marking as won, clear other won stages
        if (!empty($validated['is_won'])) {
            LeadPipelineStage::where('is_won', true)->where('id', '!=', $id)->update(['is_won' => false]);
        }
        if (!empty($validated['is_lost'])) {
            LeadPipelineStage::where('is_lost', true)->where('id', '!=', $id)->update(['is_lost' => false]);
        }

        $stage->update($validated);

        return redirect()->back()->with('success', "Pipeline stage \"{$stage->name}\" updated.");
    }

    /**
     * Delete a pipeline stage.
     */
    public function destroy($id)
    {
        $stage = LeadPipelineStage::withCount('leads')->findOrFail($id);

        if ($stage->leads_count > 0) {
            return redirect()->back()->with('error', "Cannot delete stage \"{$stage->name}\" — it has {$stage->leads_count} leads. Move them first.");
        }

        if ($stage->is_default) {
            return redirect()->back()->with('error', 'Cannot delete the default stage.');
        }

        $stage->delete();
        return redirect()->back()->with('success', "Pipeline stage \"{$stage->name}\" deleted.");
    }

    /**
     * Reorder pipeline stages.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'stages' => 'required|array',
            'stages.*.id' => 'required|exists:lead_pipeline_stages,id',
            'stages.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['stages'] as $stageData) {
            LeadPipelineStage::where('id', $stageData['id'])->update(['position' => $stageData['position']]);
        }

        return response()->json(['message' => 'Stages reordered successfully.']);
    }
}
