<?php

namespace App\Repositories\Eloquent;

use App\Models\Lead;
use App\Models\LeadPipelineStage;
use App\Repositories\Interfaces\LeadRepositoryInterface;

class LeadRepository extends BaseRepository implements LeadRepositoryInterface
{
    public function __construct(Lead $model)
    {
        parent::__construct($model);
    }

    public function getWithRelations()
    {
        return $this->model->with(['stage', 'client', 'currency', 'assignedUser'])->latest()->get();
    }

    public function getGroupedByStage()
    {
        $stages = LeadPipelineStage::ordered()->get();
        $leads = $this->model->with(['stage', 'client', 'currency', 'assignedUser'])
            ->orderBy('position')
            ->get();

        return $stages->map(function ($stage) use ($leads) {
            $stage->leads = $leads->where('lead_pipeline_stage_id', $stage->id)->values();
            return $stage;
        });
    }

    public function getRecent($limit = 5)
    {
        return $this->model->with(['stage', 'assignedUser'])->latest()->limit($limit)->get();
    }

    public function updateStage($id, $stageId, $position)
    {
        $lead = $this->find($id);
        $lead->update([
            'lead_pipeline_stage_id' => $stageId,
            'position' => $position,
        ]);

        // Check if the stage is a "won" or "lost" stage
        $stage = LeadPipelineStage::find($stageId);
        if ($stage && $stage->is_won && !$lead->won_at) {
            $lead->update(['won_at' => now(), 'lost_at' => null, 'lost_reason' => null]);
        } elseif ($stage && $stage->is_lost && !$lead->lost_at) {
            $lead->update(['lost_at' => now(), 'won_at' => null]);
        } elseif ($stage && !$stage->is_won && !$stage->is_lost) {
            $lead->update(['won_at' => null, 'lost_at' => null, 'lost_reason' => null]);
        }

        return $lead->fresh(['stage', 'client', 'currency', 'assignedUser']);
    }

    public function getTotalValue()
    {
        return $this->model->whereNull('lost_at')->sum('value') ?? 0;
    }

    public function getConversionRate()
    {
        $total = $this->model->count();
        if ($total === 0) return 0;
        $won = $this->model->whereNotNull('won_at')->count();
        return round(($won / $total) * 100, 1);
    }
}
