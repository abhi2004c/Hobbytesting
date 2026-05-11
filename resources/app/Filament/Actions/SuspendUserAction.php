<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class SuspendUserAction
{
    public static function make(): Action
    {
        return Action::make('suspend')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Suspend User')
            ->modalDescription('This user will be unable to access the platform.')
            ->form([
                Textarea::make('reason')
                    ->label('Suspension Reason')
                    ->required()
                    ->maxLength(500),
            ])
            ->action(function ($record, array $data) {
                $record->update(['status' => 'suspended']);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['reason' => $data['reason']])
                    ->log('User suspended');

                Notification::make()
                    ->title("User {$record->name} suspended")
                    ->body("Reason: {$data['reason']}")
                    ->warning()
                    ->send();
            })
            ->visible(fn ($record) => $record->status !== 'suspended');
    }
}
