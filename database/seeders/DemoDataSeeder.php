<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\GroupPrivacy;
use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use App\Enums\PostType;
use App\Enums\RsvpStatus;
use App\Models\Comment;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Group;
use App\Models\GroupCategory;
use App\Models\GroupMembership;
use App\Models\Interest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@hobbyhub.test'],
            [
                'name'              => 'Admin User',
                'email'             => 'admin@hobbyhub.test',
                'password'          => Hash::make('password'),
                'bio'               => 'Platform administrator for HobbyHub.',
                'city'              => 'San Francisco',
                'country'           => 'US',
                'is_verified'       => true,
                'status'            => 'active',
                'email_verified_at' => now(),
            ],
        );
        $admin->assignRole('platform_admin');

        // 2. Create demo users
        $users = collect();
        $demoUsers = [
            ['name' => 'Sarah Chen',     'email' => 'sarah@hobbyhub.test',  'city' => 'New York',      'bio' => 'Photographer & hiking enthusiast 📸🥾'],
            ['name' => 'Marcus Johnson', 'email' => 'marcus@hobbyhub.test', 'city' => 'Los Angeles',   'bio' => 'Guitar player and coffee lover ☕🎸'],
            ['name' => 'Priya Patel',    'email' => 'priya@hobbyhub.test',  'city' => 'Mumbai',        'bio' => 'Full-stack developer & yoga practitioner 💻🧘'],
            ['name' => 'Tom Baker',      'email' => 'tom@hobbyhub.test',    'city' => 'London',        'bio' => 'Board game collector and BBQ master 🎲🍖'],
            ['name' => 'Yuki Tanaka',    'email' => 'yuki@hobbyhub.test',   'city' => 'Tokyo',         'bio' => 'Watercolor artist and book worm 🎨📚'],
            ['name' => 'Elena Rossi',    'email' => 'elena@hobbyhub.test',  'city' => 'Rome',          'bio' => 'Runner, baker, and meditation enthusiast 🏃🧁'],
            ['name' => 'James Wilson',   'email' => 'james@hobbyhub.test',  'city' => 'Sydney',        'bio' => 'Rock climbing addict and open-source contributor 🧗💻'],
            ['name' => 'Fatima Al-Rashid','email'=> 'fatima@hobbyhub.test', 'city' => 'Dubai',         'bio' => 'Astrophotography & stargazing enthusiast 🔭🌟'],
        ];

        foreach ($demoUsers as $data) {
            $users->push(User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'          => Hash::make('password'),
                    'country'           => 'US',
                    'is_verified'       => true,
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]),
            ));
        }
        $users->push($admin);

        // Assign interests to users
        $interests = Interest::all();
        if ($interests->isNotEmpty()) {
            $users->each(fn (User $u) => $u->interests()->syncWithoutDetaching(
                $interests->random(min(4, $interests->count()))->pluck('id'),
            ));
        }

        // 3. Create demo groups
        $categories = GroupCategory::all();
        $groups = collect();

        $demoGroups = [
            ['name' => 'NYC Street Photographers',  'desc' => 'Capturing the pulse of New York City through street photography. Weekly walks, monthly exhibitions, and gear reviews.', 'privacy' => GroupPrivacy::Public, 'cat' => 'Photography'],
            ['name' => 'Bay Area Hikers',            'desc' => 'Explore trails across the Bay Area. All skill levels welcome — from gentle strolls to summit pushes!', 'privacy' => GroupPrivacy::Public, 'cat' => 'Outdoors'],
            ['name' => 'Code & Coffee Meetup',       'desc' => 'Weekly co-working sessions at local coffee shops. Bring your laptop, share knowledge, and caffeinate.', 'privacy' => GroupPrivacy::Public, 'cat' => 'Tech & Gaming'],
            ['name' => 'Guitar Jammers Club',        'desc' => 'Jam sessions every Saturday. Acoustic, electric, bass — all welcome. Learn, play, and create together.', 'privacy' => GroupPrivacy::Public, 'cat' => 'Music'],
            ['name' => 'Sourdough Bakers Society',   'desc' => 'Share your starter, swap recipes, and troubleshoot crumb structure. From beginners to artisans.', 'privacy' => GroupPrivacy::Private, 'cat' => 'Food & Drink'],
            ['name' => 'Mindful Mornings',           'desc' => 'Daily meditation practice group. Guided sessions at 7 AM. Build your practice with community support.', 'privacy' => GroupPrivacy::Public, 'cat' => 'Wellness'],
        ];

        foreach ($demoGroups as $i => $g) {
            $cat = $categories->firstWhere('name', $g['cat']) ?? $categories->first();
            $owner = $users[$i % $users->count()];

            $group = Group::firstOrCreate(
                ['slug' => Str::slug($g['name']) . '-' . Str::lower(Str::random(4))],
                [
                    'owner_id'    => $owner->id,
                    'category_id' => $cat->id,
                    'name'        => $g['name'],
                    'slug'        => Str::slug($g['name']) . '-' . Str::lower(Str::random(4)),
                    'description' => $g['desc'],
                    'privacy'     => $g['privacy']->value,
                    'location'    => $owner->city,
                    'is_verified' => $i < 3,
                    'settings'    => [],
                ],
            );

            // Add owner membership
            GroupMembership::firstOrCreate(
                ['group_id' => $group->id, 'user_id' => $owner->id],
                ['role' => MemberRole::Owner->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()->subDays(30)],
            );

            // Add random members
            $memberPool = $users->where('id', '!=', $owner->id)->random(min(4, $users->count() - 1));
            foreach ($memberPool as $member) {
                GroupMembership::firstOrCreate(
                    ['group_id' => $group->id, 'user_id' => $member->id],
                    ['role' => MemberRole::Member->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()->subDays(rand(1, 20))],
                );
            }

            $group->refreshMemberCount();
            $groups->push($group);
        }

        // 4. Create demo events
        foreach ($groups->take(4) as $group) {
            $event = Event::firstOrCreate(
                ['slug' => Str::slug($group->name . ' meetup') . '-' . Str::lower(Str::random(4))],
                [
                    'group_id'    => $group->id,
                    'creator_id'  => $group->owner_id,
                    'title'       => $group->name . ' Weekly Meetup',
                    'slug'        => Str::slug($group->name . ' meetup') . '-' . Str::lower(Str::random(4)),
                    'description' => 'Join us for our regular weekly meetup! Great opportunity to connect, share, and learn together.',
                    'type'        => EventType::InPerson->value,
                    'location'    => $group->location ?? 'TBD',
                    'starts_at'   => now()->addDays(rand(3, 14))->setHour(18)->setMinute(0),
                    'ends_at'     => now()->addDays(rand(3, 14))->setHour(20)->setMinute(0),
                    'capacity'    => 25,
                    'status'      => EventStatus::Published->value,
                ],
            );

            // Add RSVPs
            $group->members->take(3)->each(function (User $user) use ($event) {
                EventRsvp::firstOrCreate(
                    ['event_id' => $event->id, 'user_id' => $user->id],
                    ['status' => RsvpStatus::Going->value],
                );
            });
        }

        // 5. Create demo posts
        $postContent = [
            'Just discovered an amazing trail this weekend! The views from the summit were absolutely breathtaking. Who wants to join next time? 🏔️',
            'Sharing my latest project — built a full-stack app using Laravel and Livewire in just 2 weeks. Happy to answer any questions! 💻',
            'Tonight\'s jam session was incredible! We had 8 guitarists and even a surprise saxophone player. Music truly brings people together. 🎵',
            'My sourdough finally has the perfect open crumb! After 47 attempts, I found the right hydration ratio. Posting the recipe below... 🍞',
            'Morning meditation session was magical today. 15 minutes of silence, then we shared our reflections. Feeling centered and grateful. 🧘',
            'Photo walk through downtown was a success! Here are some of my favorites from today. Let me know which ones you like best! 📸',
        ];

        foreach ($groups as $i => $group) {
            if (! isset($postContent[$i])) continue;

            $author = $group->members->first() ?? $users->first();

            $post = Post::firstOrCreate(
                ['content' => $postContent[$i], 'group_id' => $group->id],
                [
                    'group_id'       => $group->id,
                    'user_id'        => $author->id,
                    'type'           => PostType::Text->value,
                    'content'        => $postContent[$i],
                    'is_pinned'      => false,
                    'is_announcement' => $i === 0,
                    'likes_count'    => rand(3, 20),
                    'comments_count' => rand(1, 8),
                ],
            );

            // Add a comment
            Comment::firstOrCreate(
                ['post_id' => $post->id, 'user_id' => $users->random()->id],
                [
                    'post_id'     => $post->id,
                    'user_id'     => $users->where('id', '!=', $author->id)->random()->id,
                    'content'     => 'This is awesome! Count me in for the next one! 🙌',
                    'likes_count' => rand(0, 5),
                ],
            );
        }

        $this->command->info('✅ Demo data seeded: ' . $users->count() . ' users, ' . $groups->count() . ' groups, events, and posts.');
    }
}
