<?php

namespace App\Jobs;

use App\Models\LeadGetterTask;
use App\Services\LeadGetterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchLeadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public LeadGetterTask $task
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LeadGetterService $service): void
    {
        Log::info("FetchLeadsJob started for task {$this->task->id}");

        $this->task->update(['status' => 'running']);

        try {
            $count = $service->fetchLeads($this->task);

            $this->task->update([
                'status' => 'completed',
                'total_results' => $count,
                'error_message' => null,
            ]);

            Log::info("FetchLeadsJob completed for task {$this->task->id}. Fetched {$count} results.");
        } catch (\Exception $e) {
            Log::error("FetchLeadsJob failed for task {$this->task->id}: {$e->getMessage()}");

            $this->task->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("FetchLeadsJob permanently failed for task {$this->task->id}: {$exception->getMessage()}");

        $this->task->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
