<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionMessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $parentId;
    public $projectId;

    public function __construct($projectId, $messageId, $parentId = null)
    {
        $this->projectId = $projectId;
        $this->messageId = $messageId;
        $this->parentId = $parentId;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('project.' . $this->projectId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'discussion.message.deleted';
    }
}
