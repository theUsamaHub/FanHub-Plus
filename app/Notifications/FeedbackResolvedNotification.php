<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class FeedbackResolvedNotification extends Notification
{
    public function __construct(
        public Feedback $feedback
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your feedback has been resolved')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('Your ' . $this->feedback->type . ' feedback has been marked as resolved by the ' . config('app.name') . ' team.')
            ->line('You reported: "' . Str::limit($this->feedback->message, 160) . '"')
            ->action('View My Feedback', route('user.feedback'))
            ->line('You can open a new feedback any time if something else comes up.');
    }
}
