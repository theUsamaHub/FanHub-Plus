<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use Illuminate\Database\Seeder;

class UserFavoriteCategorySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $count = 0;
        foreach ($users as $user) {
            foreach ($categories as $category) {
                if ($count >= 10) {
                    break 2;
                }

                UserFavoriteCategory::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                    ],
                    [
                        'created_at' => now(),
                    ]
                );
                $count++;
            }
        }
    }
}
