<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ReportStatus;
use App\Models\Report;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-flag';
    protected static string | \UnitEnum | null $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 1;
    protected static string | \Illuminate\Contracts\Support\Htmlable | null $navigationBadgeTooltip = 'Pending Reports';

    public static function getNavigationBadge(): ?string
    {
        $count = Report::where('status', ReportStatus::Pending)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = Report::where('status', ReportStatus::Pending)->count();
        return $count > 10 ? 'danger' : ($count > 0 ? 'warning' : null);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Report Details')->schema([
                Forms\Components\TextInput::make('reason')->disabled(),
                Forms\Components\Textarea::make('description')->disabled()->columnSpanFull(),
                Forms\Components\TextInput::make('reportable_type')
                    ->label('Content Type')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options(array_combine(
                        array_column(ReportStatus::cases(), 'value'),
                        array_column(ReportStatus::cases(), 'name'),
                    )),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reportable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Reporter')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(30),
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'reviewed' => 'info',
                        'resolved' => 'success',
                        'dismissed'=> 'gray',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(
                        array_column(ReportStatus::cases(), 'value'),
                        array_column(ReportStatus::cases(), 'name'),
                    )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Resolution Note')
                            ->required(),
                    ])
                    ->action(function (Report $record, array $data) {
                        $record->update([
                            'status'      => ReportStatus::Resolved,
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);
                        Notification::make()->title('Report resolved')->success()->send();
                    })
                    ->visible(fn (Report $record) => $record->status === ReportStatus::Pending),
                Tables\Actions\Action::make('dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn (Report $record) => $record->update(['status' => ReportStatus::Dismissed]))
                    ->visible(fn (Report $record) => $record->status === ReportStatus::Pending),
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
            'index' => ReportResource\Pages\ListReports::route('/'),
        ];
    }
}
