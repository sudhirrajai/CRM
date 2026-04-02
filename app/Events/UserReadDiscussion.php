<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserReadDiscussion implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectId;
    public $userId;
    public $userName;
    public $lastReadMessageId;

    public function __construct($projectId, $userId, $userName, $lastReadMessageId)
    {
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->lastReadMessageId = $lastReadMessageId;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('project.' . $this->projectId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'discussion.read';
    }
}
