<?php

namespace App\Jobs;

use App\Mail\MentionReminderMail;
use App\Models\DiscussionRead;
use App\Models\ProjectDiscussion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMentionReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $discussion;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(ProjectDiscussion $discussion, $userId)
    {
        $this->discussion = $discussion;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user) return;

        // Check if the user has read messages past this one
        $read = DiscussionRead::where('project_id', $this->discussion->project_id)
            ->where('user_id', $this->userId)
            ->first();

        // If no read record OR last read is older than this message's creation
        if (!$read || $read->last_read_at < $this->discussion->created_at) {
            Mail::to($user->email)->send(new MentionReminderMail($this->discussion, $user));
        }
    }
}
