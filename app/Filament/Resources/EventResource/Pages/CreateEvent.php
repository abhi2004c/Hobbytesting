<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] ??= \Illuminate\Support\Str::slug($data['title'])
            .'-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5));

        return $data;
    }
}