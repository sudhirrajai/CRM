<?php

namespace App\Listeners;

use App\Events\NewDiscussionMessage;
use App\Models\User;
use App\Notifications\NewDiscussionMessageNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendNewDiscussionMessageNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(NewDiscussionMessage $event): void
    {
        $message = $event->message;
        $senderId = $message->user_id;

        $participants = collect();

        if ($message->project_id) {
            $project = $message->project;
            $admins = User::role('admin')->get();
            $assignedStaff = $project->members()->role('staff')->get();
            $clientUsers = User::where('client_id', $project->client_id)->get();
            $participants = $admins->concat($assignedStaff)->concat($clientUsers);
        } elseif ($message->group_id) {
            $group = $message->group;
            $admins = User::role('admin')->get();
            $groupMembers = $group->members;
            $participants = $admins->concat($groupMembers);
        }

        // Filter out the sender and get unique users
        $recipients = $participants->unique('id')->reject(function ($user) use ($senderId) {
            return $user->id === $senderId;
        });

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewDiscussionMessageNotification($message));
        }
    }
}
