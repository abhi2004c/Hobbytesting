<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static string | \UnitEnum | null $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Activity Log';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->default('System')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->wrap()
                    ->limit(80),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->badge(),
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Log')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->options(fn () => Activity::distinct()->pluck('log_name', 'log_name')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ActivityLogResource\Pages\ListActivities::route('/'),
        ];
    }
}
