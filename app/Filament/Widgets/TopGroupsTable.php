<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Group;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopGroupsTable extends BaseWidget
{
    protected static ?string $heading = 'Top Groups by Members';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Group::query()->orderByDesc('member_count_cache')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->badge(),
                Tables\Columns\TextColumn::make('member_count_cache')->label('Members')->sortable()->numeric(),
                Tables\Columns\TextColumn::make('owner.name')->label('Owner'),
                Tables\Columns\IconColumn::make('is_verified')->boolean(),
            ])
            ->paginated(false);
    }
}
