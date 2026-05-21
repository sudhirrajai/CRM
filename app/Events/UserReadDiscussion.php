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
    public $groupId;
    public $userId;
    public $userName;
    public $lastReadMessageId;

    public function __construct($projectId, $userId, $userName, $lastReadMessageId, $groupId = null)
    {
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->lastReadMessageId = $lastReadMessageId;
        $this->groupId = $groupId;
    }

    public function broadcastOn(): array
    {
        if ($this->projectId) {
            return [
                new PresenceChannel('project.' . $this->projectId),
            ];
        }

        return [
            new PresenceChannel('group.' . $this->groupId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'discussion.read';
    }
}
