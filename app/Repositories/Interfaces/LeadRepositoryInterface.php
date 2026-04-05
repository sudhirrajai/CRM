<?php

namespace App\Repositories\Interfaces;

interface LeadRepositoryInterface extends BaseRepositoryInterface
{
    public function getWithRelations();
    public function getGroupedByStage();
    public function getRecent($limit = 5);
    public function updateStage($id, $stageId, $position);
    public function getTotalValue();
    public function getConversionRate();
}
