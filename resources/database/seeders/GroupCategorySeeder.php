<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GroupCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GroupCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Sports & Fitness', 'icon' => '⚡', 'color' => '#ef4444', 'description' => 'Stay active with sports and fitness groups'],
            ['name' => 'Arts & Crafts',    'icon' => '🎨', 'color' => '#f59e0b', 'description' => 'Express yourself through creative arts'],
            ['name' => 'Tech & Gaming',    'icon' => '💻', 'color' => '#3b82f6', 'description' => 'Technology enthusiasts and gamers'],
            ['name' => 'Music',            'icon' => '🎵', 'color' => '#8b5cf6', 'description' => 'Musicians and music lovers'],
            ['name' => 'Outdoors',         'icon' => '🌲', 'color' => '#10b981', 'description' => 'Nature and outdoor adventures'],
            ['name' => 'Food & Drink',     'icon' => '🍳', 'color' => '#f97316', 'description' => 'Cooking, baking, and culinary explorations'],
            ['name' => 'Photography',      'icon' => '📷', 'color' => '#06b6d4', 'description' => 'Capture the world through your lens'],
            ['name' => 'Books & Writing',  'icon' => '📚', 'color' => '#84cc16', 'description' => 'Readers and writers community'],
            ['name' => 'Wellness',         'icon' => '🧘', 'color' => '#14b8a6', 'description' => 'Mental health, meditation, and wellness'],
            ['name' => 'Travel',           'icon' => '✈️', 'color' => '#6366f1', 'description' => 'Explore the world together'],
            ['name' => 'Social',           'icon' => '🤝', 'color' => '#ec4899', 'description' => 'Social clubs and meetups'],
            ['name' => 'Other',            'icon' => '🌟', 'color' => '#64748b', 'description' => 'Miscellaneous groups and interests'],
        ];

        foreach ($categories as $i => $cat) {
            GroupCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, [
                    'slug'      => Str::slug($cat['name']),
                    'is_active' => true,
                    'order'     => $i,
                ]),
            );
        }
    }
}
