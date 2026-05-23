<?php

namespace App\Notifications;

use App\Models\ProjectDiscussion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class NewDiscussionMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProjectDiscussion $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->message->project_id 
            ? "New message in project: {$this->message->project->name}" 
            : "New message in group: {$this->message->group->name}";

        $url = $this->message->project_id 
            ? url('/discussions?project_id=' . $this->message->project_id)
            : url('/discussions?group_id=' . $this->message->group_id);

        return [
            'message_id' => $this->message->id,
            'title' => $title,
            'body' => $this->message->user->name . ': ' . strip_tags($this->message->message),
            'url' => $url,
            'user_id' => $this->message->user_id,
            'user_name' => $this->message->user->name,
            'project_id' => $this->message->project_id,
            'group_id' => $this->message->group_id,
        ];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        $data = $this->toArray($notifiable);
        $tag = $this->message->project_id 
            ? "project-{$this->message->project_id}" 
            : "group-{$this->message->group_id}";
        
        return (new WebPushMessage)
            ->title($data['title'])
            ->icon('/assets/images/favicon.png')
            ->badge('/assets/images/logo-sm.png')
            ->body($data['body'])
            ->tag($tag)
            ->renotify()
            ->vibrate([100, 50, 100])
            ->action('View Message', 'view_message')
            ->data(['url' => $data['url']]);
    }
}
