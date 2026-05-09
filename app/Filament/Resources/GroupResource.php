<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Group Details')->schema([
                TextInput::make('name')->required(),
                Textarea::make('description')->required(),
                Toggle::make('is_verified')->label('Verified'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('member_count_cache')->label('Members')->numeric(),
                Tables\Columns\IconColumn::make('is_verified')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => GroupResource\Pages\ListGroups::route('/'),
            'create' => GroupResource\Pages\CreateGroup::route('/create'),
            'view' => GroupResource\Pages\ViewGroup::route('/{record}'),
            'edit' => GroupResource\Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
