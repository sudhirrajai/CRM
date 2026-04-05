<?php

namespace Database\Seeders;

use App\Models\LeadPipelineStage;
use Illuminate\Database\Seeder;

class LeadPipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'New',           'color' => '#6c757d', 'position' => 0, 'is_default' => true,  'is_won' => false, 'is_lost' => false],
            ['name' => 'Contacted',     'color' => '#0dcaf0', 'position' => 1, 'is_default' => false, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Qualified',     'color' => '#6f42c1', 'position' => 2, 'is_default' => false, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Proposal Sent', 'color' => '#fd7e14', 'position' => 3, 'is_default' => false, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Negotiation',   'color' => '#ffc107', 'position' => 4, 'is_default' => false, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Won',           'color' => '#198754', 'position' => 5, 'is_default' => false, 'is_won' => true,  'is_lost' => false],
            ['name' => 'Lost',          'color' => '#dc3545', 'position' => 6, 'is_default' => false, 'is_won' => false, 'is_lost' => true],
        ];

        foreach ($stages as $stage) {
            LeadPipelineStage::firstOrCreate(
                ['name' => $stage['name']],
                $stage
            );
        }
    }
}
