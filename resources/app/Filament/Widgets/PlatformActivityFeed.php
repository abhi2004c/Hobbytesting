<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Activitylog\Models\Activity;

class PlatformActivityFeed extends BaseWidget
{
    protected static ?string $heading = 'Recent Platform Activity';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->default('System'),
                Tables\Columns\TextColumn::make('description')
                    ->wrap()
                    ->limit(60),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
