<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Auth\DTOs\SocialAuthDTO;
use Laravel\Socialite\Facades\Socialite;

final class SocialAuthService
{
    private const SUPPORTED = ['google', 'github'];

    public function getRedirectUrl(string $provider): string
    {
        $this->guardProvider($provider);

        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    public function handleCallback(string $provider): SocialAuthDTO
    {
        $this->guardProvider($provider);

        $user = Socialite::driver($provider)->stateless()->user();

        return SocialAuthDTO::fromRequest([
            'provider'    => $provider,
            'provider_id' => $user->getId(),
            'email'       => $user->getEmail(),
            'name'        => $user->getName() ?? $user->getNickname() ?? 'User',
            'avatar'      => $user->getAvatar(),
        ]);
    }

    private function guardProvider(string $provider): void
    {
        throw_unless(
            in_array($provider, self::SUPPORTED, true),
            \InvalidArgumentException::class,
            "Unsupported OAuth provider: {$provider}",
        );
    }
}