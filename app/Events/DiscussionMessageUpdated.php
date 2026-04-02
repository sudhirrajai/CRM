<?php

namespace App\Events;

use App\Models\ProjectDiscussion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionMessageUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ProjectDiscussion $message)
    {
        $this->message = $message->load(['user', 'attachments', 'replies.user', 'replies.attachments']);
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('project.' . $this->message->project_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'discussion.message.updated';
    }
}
