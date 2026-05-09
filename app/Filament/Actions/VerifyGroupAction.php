<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class VerifyGroupAction
{
    public static function make(): Action
    {
        return Action::make('verify')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Verify Group')
            ->modalDescription('This will mark the group as officially verified.')
            ->action(function ($record) {
                $record->update(['is_verified' => true]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->log('Group verified');

                Notification::make()
                    ->title("Group \"{$record->name}\" verified")
                    ->success()
                    ->send();
            })
            ->visible(fn ($record) => ! $record->is_verified);
    }
}
