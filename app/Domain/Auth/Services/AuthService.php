<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Auth\DTOs\LoginDTO;
use App\Domain\Auth\DTOs\RegisterDTO;
use App\Domain\Auth\DTOs\SocialAuthDTO;
use App\Domain\Auth\Exceptions\AccountSuspendedException;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Facades\Activity;

final class AuthService
{
    public function register(RegisterDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            /** @var User $user */
            $user = User::create([
                'name'              => $dto->name,
                'email'             => $dto->email,
                'password'          => $dto->password,
                'city'              => $dto->city,
                'country'           => $dto->country,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);

            $user->assignRole('member');

            event(new Registered($user));

            SendWelcomeEmailJob::dispatch($user)->onQueue('emails');

            Activity::causedBy($user)
                ->performedOn($user)
                ->event('registered')
                ->log('User registered');

            return $user;
        });
    }

    /**
     * @return array{user: User, token: string, expires_at: Carbon}
     */
    public function login(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (! $user || ! $user->password || ! Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException('The provided credentials are incorrect.');
        }

        if ($user->isSuspended()) {
            throw new AccountSuspendedException('Your account has been suspended.');
        }

        $user->recordLogin();

        $expiresAt = $dto->remember ? now()->addDays(30) : now()->addDay();

        $token = $user->createToken(
            $dto->deviceName ?? 'web',
            ['*'],
            $expiresAt,
        )->plainTextToken;

        Activity::causedBy($user)
            ->event('logged_in')
            ->withProperties(['ip' => $dto->ipAddress])
            ->log('User logged in');

        return [
            'user'       => $user,
            'token'      => $token,
            'expires_at' => $expiresAt,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();

        Activity::causedBy($user)->event('logged_out')->log('User logged out');
    }

    /**
     * @return array{user: User, token: string, expires_at: Carbon, is_new: bool}
     */
    public function handleSocialLogin(SocialAuthDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $isNew = false;

            throw_if(
                $dto->email === null,
                \DomainException::class,
                'Your ' . $dto->provider . ' account has no public email. Please add one and try again.',
            );

            $user = User::where('email', $dto->email)
                ->orWhere('google_id', $dto->providerId)
                ->first();

            if (! $user) {
                $user = User::create([
                    'name'              => $dto->name,
                    'email'             => $dto->email,
                    'avatar'            => $dto->avatar,
                    'google_id'         => $dto->provider === 'google' ? $dto->providerId : null,
                    'email_verified_at' => now(),
                    'is_verified'       => true,
                    'status'            => 'active',
                ]);

                $user->assignRole('member');
                $isNew = true;

                SendWelcomeEmailJob::dispatch($user)->onQueue('emails');
            } elseif ($dto->provider === 'google' && ! $user->google_id) {
                $user->forceFill(['google_id' => $dto->providerId])->save();
            }

            if ($user->isSuspended()) {
                throw new AccountSuspendedException('Your account has been suspended.');
            }

            $user->recordLogin();

            $expiresAt = now()->addDays(30);
            $token     = $user->createToken("social-{$dto->provider}", ['*'], $expiresAt)->plainTextToken;

            return [
                'user'       => $user,
                'token'      => $token,
                'expires_at' => $expiresAt,
                'is_new'     => $isNew,
            ];
        });
    }
}