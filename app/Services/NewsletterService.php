<?php

namespace App\Services;

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewsletterService
{
    /**
     * Send synchronously so the caller can report honest sent/failed counts
     * and subscribers get their email immediately.
     *
     * @return array{sent: int, failed: int}
     */
    public function send(Newsletter $newsletter, $recipients): array
    {
        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterMail($newsletter, $subscriber));

                $sentCount++;

                $subscriber->increment('email_count');
                $subscriber->forceFill(['last_email_sent_at' => now()])->save();
            } catch (\Throwable $e) {
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

        return ['sent' => $sentCount, 'failed' => $failedCount];
    }

    public function getRecipients(array $filters)
    {
        $query = Subscriber::query()->active();

        $categories = array_values(array_filter((array) ($filters['categories'] ?? [])));

        if ($categories) {
            $query->where(function ($q) use ($categories) {
                foreach ($categories as $categoryId) {
                    $q->orWhereJsonContains('preferences->categories', (int) $categoryId);
                }
            });
        }

        return $query->get();
    }
}
