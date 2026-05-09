<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\GroupPrivacy;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Community';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(100),
            Forms\Components\Textarea::make('description')->required()->rows(4),
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->required(),
            Forms\Components\Select::make('privacy')
                ->options(GroupPrivacy::options())
                ->required(),
                        Forms\Components\TextInput::make('location')->maxLength(200),
            Forms\Components\TextInput::make('max_members')->numeric()->minValue(2),
            Forms\Components\Toggle::make('is_verified')->label('Verified'),
            Forms\Components\Toggle::make('is_featured')->label('Featured on homepage'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')
                    ->collection('cover')
                    ->conversion('card')
                    ->circular(false)
                    ->size(60),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Group $g) => $g->location),
                Tables\Columns\TextColumn::make('category.name')->badge(),
                Tables\Columns\TextColumn::make('privacy')
                    ->badge()
                    ->color(fn (GroupPrivacy $state) => $state->color()),
                Tables\Columns\TextColumn::make('member_count_cache')
                    ->label('Members')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner.name')->label('Owner'),
                Tables\Columns\TextColumn::make('created_at')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_verified')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('privacy')->options(GroupPrivacy::options()),
                Tables\Filters\TernaryFilter::make('is_verified'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Group $g) => ! $g->is_verified)
                    ->requiresConfirmation()
                    ->action(function (Group $g) {
                        $g->update(['is_verified' => true]);
                        Notification::make()->title('Group verified')->success()->send();
                    }),
                Tables\Actions\Action::make('feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn (Group $g) => $g->update(['is_featured' => ! $g->is_featured])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'view'   => Pages\ViewGroup::route('/{record}'),
            'edit'   => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}