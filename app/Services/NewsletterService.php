<?php

namespace App\Services;

use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewsletterService
{
    public function send(Newsletter $newsletter, $recipients): void
    {
        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new \App\Mail\NewsletterMail(
                    $newsletter,
                    $subscriber
                ));
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Newsletter send failed', [
                    'newsletter_id' => $newsletter->id,
                    'subscriber_id' => $subscriber->id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $newsletter->update([
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);
    }

    public function getRecipients(array $filters)
    {
        $query = Subscriber::query()->where('status', 'active');

        if (!empty($filters['categories'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['categories'] as $categoryId) {
                    $q->orWhereJsonContains('preferences->categories', $categoryId);
                }
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'active') {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }
}