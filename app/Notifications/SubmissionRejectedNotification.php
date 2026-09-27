<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionRejectedNotification extends Notification
{
    public function __construct(
        public Content $content
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your submission was not approved')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('We reviewed your submission "' . $this->content->title . '" and it has not been approved for publishing right now.')
            ->line('You can edit the submission from your account and send it again for review.')
            ->action('View My Submissions', route('user.submissions'))
            ->line('If you have questions, reply from your feedback page and we will take a look.');
    }
}
