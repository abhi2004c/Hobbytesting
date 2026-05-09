<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $categories = config('community.supported_interest_categories', []);

        foreach ($categories as $category => $interests) {
            foreach ($interests as $name) {
                Interest::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'name'     => $name,
                        'slug'     => Str::slug($name),
                        'category' => $category,
                        'icon'     => $this->getIcon($name),
                    ],
                );
            }
        }
    }

    private function getIcon(string $name): string
    {
        $icons = [
            'Running' => '🏃', 'Cycling' => '🚴', 'Yoga' => '🧘', 'Football' => '⚽',
            'Basketball' => '🏀', 'Climbing' => '🧗', 'Swimming' => '🏊', 'Martial Arts' => '🥋',
            'Painting' => '🎨', 'Pottery' => '🏺', 'Sketching' => '✏️', 'Knitting' => '🧶',
            'Calligraphy' => '✒️', 'Origami' => '📄', 'Programming' => '💻', 'Board Games' => '🎲',
            'Video Games' => '🎮', 'AI/ML' => '🤖', 'Open Source' => '🌐', 'Hardware Hacking' => '🔧',
            'Guitar' => '🎸', 'Piano' => '🎹', 'Singing' => '🎤', 'DJing' => '🎧',
            'Production' => '🎛️', 'Choir' => '🎶', 'Hiking' => '🥾', 'Camping' => '⛺',
            'Birdwatching' => '🐦', 'Fishing' => '🎣', 'Stargazing' => '🌟',
            'Cooking' => '👨‍🍳', 'Baking' => '🧁', 'Wine Tasting' => '🍷', 'Coffee' => '☕', 'BBQ' => '🍖',
            'Street' => '📸', 'Portrait' => '🖼️', 'Wildlife' => '🦁', 'Astrophotography' => '🔭',
            'Book Club' => '📚', 'Creative Writing' => '📝', 'Poetry' => '🪶', 'Journaling' => '📓',
            'Meditation' => '🧘‍♂️', 'Mental Health' => '💚', 'Mindfulness' => '🌿',
            'Backpacking' => '🎒', 'Road Trips' => '🚗', 'Solo Travel' => '✈️',
        ];

        return $icons[$name] ?? '🎯';
    }
}
