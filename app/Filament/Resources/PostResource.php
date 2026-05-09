<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PostType;
use App\Models\Post;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static string | \UnitEnum | null $navigationGroup = 'Community';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Post Details')->schema([
                Forms\Components\Textarea::make('content')->maxLength(5000)->columnSpanFull(),
                Forms\Components\Toggle::make('is_pinned'),
                Forms\Components\Toggle::make('is_announcement'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Author')->searchable(),
                Tables\Columns\TextColumn::make('group.name')->label('Group')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'text'  => 'primary',
                        'link'  => 'info',
                        'image' => 'success',
                        'poll'  => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('content')->limit(50)->wrap(),
                Tables\Columns\TextColumn::make('likes_count')->label('❤️')->sortable(),
                Tables\Columns\TextColumn::make('comments_count')->label('💬')->sortable(),
                Tables\Columns\IconColumn::make('is_pinned')->boolean(),
                Tables\Columns\IconColumn::make('is_announcement')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_combine(
                        array_column(PostType::cases(), 'value'),
                        array_column(PostType::cases(), 'name'),
                    )),
                Tables\Filters\TernaryFilter::make('is_pinned'),
                Tables\Filters\TernaryFilter::make('is_announcement'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('pin')
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(fn (Post $p) => $p->update(['is_pinned' => ! $p->is_pinned]))
                    ->label(fn (Post $p) => $p->is_pinned ? 'Unpin' : 'Pin'),
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
            'index' => PostResource\Pages\ListPosts::route('/'),
        ];
    }
}
