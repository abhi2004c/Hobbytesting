<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Event Details')->schema([
                TextInput::make('title')->required(),
                Textarea::make('description'),
                DateTimePicker::make('starts_at')->required(),
                DateTimePicker::make('ends_at')->required(),
                TextInput::make('status')->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('starts_at')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => EventResource\Pages\ListEvents::route('/'),
            'create' => EventResource\Pages\CreateEvent::route('/create'),
            'edit' => EventResource\Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
