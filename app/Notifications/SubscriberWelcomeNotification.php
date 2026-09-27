<?php

namespace App\Notifications;

use App\Models\Subscriber;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriberWelcomeNotification extends Notification
{
    public function __construct(
        public Subscriber $subscriber
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You are subscribed to ' . config('app.name') . ' newsletters')
            ->greeting('Welcome' . ($this->subscriber->name ? ', ' . $this->subscriber->name : '') . '!')
            ->line('Thanks for subscribing to ' . config('app.name') . ' updates. You will hear from us when we publish new stories, events and drops.')
            ->line('No confirmation needed - your subscription is active right now.')
            ->action('Browse ' . config('app.name'), route('home'))
            ->line('Don\'t want these emails anymore? [Unsubscribe](' . $this->subscriber->unsubscribeUrl() . ')');
    }
}
