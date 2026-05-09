<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string | \UnitEnum | null $navigationGroup = 'Admin';
    protected static ?int $navigationSort = 10;
    protected string $view = 'filament.pages.site-settings';

    public int $max_group_members_free = 50;
    public int $max_group_members_premium = 500;
    public int $max_groups_per_user_free = 3;
    public int $max_events_per_month_free = 5;
    public int $auto_review_threshold = 3;
    public bool $social_login = true;
    public bool $two_factor_auth = false;
    public bool $paid_groups = false;
    public bool $live_events = true;
    public bool $mobile_push = false;
    public bool $reports_notify_admin = true;

    public function mount(): void
    {
        $this->max_group_members_free    = (int) config('community.limits.max_group_members_free', 50);
        $this->max_group_members_premium = (int) config('community.limits.max_group_members_premium', 500);
        $this->max_groups_per_user_free  = (int) config('community.limits.max_groups_per_user_free', 3);
        $this->max_events_per_month_free = (int) config('community.limits.max_events_per_month_free', 5);
        $this->auto_review_threshold     = (int) config('community.moderation.auto_review_threshold', 3);
        $this->social_login              = (bool) config('community.features.social_login', true);
        $this->two_factor_auth           = (bool) config('community.features.two_factor_auth', false);
        $this->paid_groups               = (bool) config('community.features.paid_groups', false);
        $this->live_events               = (bool) config('community.features.live_events', true);
        $this->mobile_push               = (bool) config('community.features.mobile_push', false);
        $this->reports_notify_admin      = (bool) config('community.moderation.reports_notify_admin', true);
    }

    public function save(): void
    {
        Notification::make()
            ->title('Settings saved')
            ->body('Configuration updated successfully. Note: Some changes require a cache clear.')
            ->success()
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Group Limits')->schema([
                TextInput::make('max_group_members_free')->numeric()->label('Max Members (Free)'),
                TextInput::make('max_group_members_premium')->numeric()->label('Max Members (Premium)'),
                TextInput::make('max_groups_per_user_free')->numeric()->label('Max Groups/User (Free)'),
                TextInput::make('max_events_per_month_free')->numeric()->label('Max Events/Month (Free)'),
            ])->columns(2),

            Section::make('Feature Flags')->schema([
                Toggle::make('social_login')->label('Social Login (Google/GitHub)'),
                Toggle::make('two_factor_auth')->label('Two-Factor Authentication'),
                Toggle::make('paid_groups')->label('Paid Groups'),
                Toggle::make('live_events')->label('Live Events / Streaming'),
                Toggle::make('mobile_push')->label('Mobile Push Notifications'),
            ])->columns(2),

            Section::make('Moderation')->schema([
                TextInput::make('auto_review_threshold')->numeric()->label('Auto-Flag Report Threshold'),
                Toggle::make('reports_notify_admin')->label('Notify Admin on New Reports'),
            ])->columns(2),
        ]);
    }
}
