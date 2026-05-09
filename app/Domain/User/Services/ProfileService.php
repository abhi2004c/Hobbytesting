<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\DTOs\UpdateProfileDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ProfileService
{
    public function update(User $user, UpdateProfileDTO $dto): User
    {
        return DB::transaction(function () use ($user, $dto) {
            $data = collect($dto->toArray())
                ->except('interestIds')
                ->mapWithKeys(fn ($v, $k) => [str($k)->snake()->toString() => $v])
                ->toArray();

            $user->update($data);

            if ($dto->interestIds !== null) {
                $user->interests()->sync($dto->interestIds);
            }

            return $user->fresh(['interests']);
        });
    }

    public function updateAvatar(User $user, \Illuminate\Http\UploadedFile $file): User
    {
        $user->addMedia($file)->toMediaCollection('avatar');

        return $user->fresh();
    }
}
