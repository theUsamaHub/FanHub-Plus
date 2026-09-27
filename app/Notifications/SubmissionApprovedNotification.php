<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionApprovedNotification extends Notification
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
        $url = $this->content->slug
            ? route('public.content', $this->content)
            : route('user.submissions');

        return (new MailMessage)
            ->subject('Your submission has been published')
            ->greeting('Good news, ' . ($notifiable->name ?? 'there') . '!')
            ->line('Your submission "' . $this->content->title . '" has been approved by the ' . config('app.name') . ' team.')
            ->line('It is now live and visible to everyone on the site.')
            ->action('View Published Content', $url)
            ->line('Thanks for contributing to ' . config('app.name') . '.');
    }
}
