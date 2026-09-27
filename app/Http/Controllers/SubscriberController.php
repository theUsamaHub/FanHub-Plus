<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Notifications\SubscriberWelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('subscribe', [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $existing = Subscriber::where('email', $validated['email'])->first();

        if ($existing) {
            $wasInactive = $existing->status !== 'active' || $existing->unsubscribed_at;

            if (! $wasInactive) {
                return back()->with('success', 'You are already subscribed!');
            }

            $existing->update([
                'status' => 'active',
                'unsubscribed_at' => null,
                'subscribed_at' => now(),
                'unsubscribe_token' => $existing->unsubscribe_token ?: Str::random(64),
            ]);

            $this->sendWelcome($existing);

            return back()->with('success', 'Welcome back! Your subscription is active again.');
        }

        $subscriber = Subscriber::create([
            'email' => $validated['email'],
            'name' => $validated['name'] ?? null,
            'subscribed_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        $this->sendWelcome($subscriber);

        return back()->with('success', 'Thank you for subscribing!');
    }

    public function unsubscribe(string $token): View
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return view('subscriber.invalid-token');
        }

        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return view('subscriber.unsubscribed');
    }

    private function sendWelcome(Subscriber $subscriber): void
    {
        try {
            $subscriber->notify(new SubscriberWelcomeNotification($subscriber));
        } catch (\Throwable $exception) {
            Log::warning('Could not send the newsletter welcome email.', [
                'subscriber_id' => $subscriber->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
