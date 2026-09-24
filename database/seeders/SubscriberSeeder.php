<?php

namespace Database\Seeders;

use App\Models\Subscriber;
use Illuminate\Database\Seeder;

class SubscriberSeeder extends Seeder
{
    public function run(): void
    {
        $subscribers = [
            ['email' => 'sub1@fanhub.com', 'name' => 'Subscriber One'],
            ['email' => 'sub2@fanhub.com', 'name' => 'Subscriber Two'],
            ['email' => 'sub3@fanhub.com', 'name' => 'Subscriber Three'],
            ['email' => 'sub4@fanhub.com', 'name' => 'Subscriber Four'],
            ['email' => 'sub5@fanhub.com', 'name' => 'Subscriber Five'],
            ['email' => 'sub6@fanhub.com', 'name' => 'Subscriber Six'],
            ['email' => 'sub7@fanhub.com', 'name' => 'Subscriber Seven'],
            ['email' => 'sub8@fanhub.com', 'name' => 'Subscriber Eight'],
            ['email' => 'sub9@fanhub.com', 'name' => 'Subscriber Nine'],
            ['email' => 'sub10@fanhub.com', 'name' => 'Subscriber Ten'],
        ];

        foreach ($subscribers as $index => $sub) {
            Subscriber::updateOrCreate(
                ['email' => $sub['email']],
                [
                    'name' => $sub['name'],
                    'subscribed_at' => now()->subDays(20 - $index),
                    'unsubscribed_at' => null,
                    'ip_address' => '127.0.0.1',
                ]
            );
        }
    }
}
