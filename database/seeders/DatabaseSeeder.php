<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Order matters: roles and users come first so the rest of the seeders
        // can attach a `submitted_by` / `user_id` when applicable. Categories
        // need to be present before the content and event seeders run, and the
        // four admin-managed tables come last so they pick up the seeded data.
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ChatbotQuerySeeder::class);

        $this->call(TagSeeder::class);
        $this->call(FeedbackSeeder::class);
        $this->call(MerchandiseItemSeeder::class);
        $this->call(EventSeeder::class);
    }
}