<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\ReportStatus;
use App\Models\Report;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ContentModeration extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static string | \UnitEnum | null $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.content-moderation';

    public function table(Table $table): Table
    {
        return $table
            ->query(Report::query()->where('status', ReportStatus::Pending))
            ->columns([
                Tables\Columns\TextColumn::make('reportable_type')
                    ->label('Content Type')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('reporter.name')->label('Reporter'),
                Tables\Columns\TextColumn::make('reason')->limit(40)->wrap(),
                Tables\Columns\TextColumn::make('description')->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('note')->required(),
                    ])
                    ->action(function (Report $record, array $data) {
                        $record->update([
                            'status'      => ReportStatus::Resolved,
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);
                        Notification::make()->title('Report resolved')->success()->send();
                    }),
                Tables\Actions\Action::make('dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn (Report $record) => $record->update(['status' => ReportStatus::Dismissed])),
            ])
            ->emptyStateHeading('No pending reports')
            ->emptyStateDescription('All reports have been handled.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
