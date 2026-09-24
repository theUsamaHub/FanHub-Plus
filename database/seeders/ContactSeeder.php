<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            ['name' => 'John Doe', 'email' => 'john.doe@example.com', 'subject' => 'Partnership Inquiry', 'message' => 'Interested in sponsoring FanHub annual convention.', 'status' => 'new'],
            ['name' => 'Alice Smith', 'email' => 'alice.smith@example.com', 'subject' => 'Media Press Inquiry', 'message' => 'Requesting press credentials for upcoming Esports championship.', 'status' => 'replied'],
            ['name' => 'Robert Johnson', 'email' => 'robert.j@example.com', 'subject' => 'Content Submissions', 'message' => 'Inquiry regarding fan community article submission guidelines.', 'status' => 'read'],
            ['name' => 'Emily Davis', 'email' => 'emily.davis@example.com', 'subject' => 'Merchandise Order Question', 'message' => 'Need help updating shipping address for my pre-order.', 'status' => 'replied'],
            ['name' => 'Michael Brown', 'email' => 'michael.b@example.com', 'subject' => 'Bug Report', 'message' => 'Form submission button on contact page stays disabled.', 'status' => 'read'],
            ['name' => 'Sophia Wilson', 'email' => 'sophia.w@example.com', 'subject' => 'Cosplay Booth Registration', 'message' => 'How can artists reserve a cosplay showcase booth?', 'status' => 'new'],
            ['name' => 'David Taylor', 'email' => 'david.t@example.com', 'subject' => 'API Access Request', 'message' => 'Requesting developer API keys for community fan app.', 'status' => 'new'],
            ['name' => 'Emma Anderson', 'email' => 'emma.a@example.com', 'subject' => 'Account Support', 'message' => 'Cannot reset password through email verification link.', 'status' => 'replied'],
            ['name' => 'James Thomas', 'email' => 'james.t@example.com', 'subject' => 'Feedback on UI Design', 'message' => 'Loving the new responsive layout and dark theme!', 'status' => 'read'],
            ['name' => 'Olivia Jackson', 'email' => 'olivia.j@example.com', 'subject' => 'Event Volunteering', 'message' => 'Interested in volunteering at Tokyo Anime Expo booth.', 'status' => 'new'],
        ];

        foreach ($contacts as $c) {
            Contact::create(array_merge($c, [
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(rand(1, 15)),
            ]));
        }
    }
}
