<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Domain\Auth\Services\SocialAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $social,
        private readonly AuthService $auth,
    ) {}

    public function redirect(string $provider): RedirectResponse
    {
        if (! config("services.{$provider}.client_id")) {
            return redirect()->route('login')
                ->withErrors(['email' => ucfirst($provider) . ' login is not configured yet.']);
        }

        return redirect()->away($this->social->getRedirectUrl($provider));
    }

    public function callback(string $provider): RedirectResponse
    {
        try {
            $dto    = $this->social->handleCallback($provider);
            $result = $this->auth->handleSocialLogin($dto);

            Auth::login($result['user'], remember: true);

            return redirect()->intended(
                $result['is_new'] ? route('profile.edit') : route('dashboard'),
            );
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Social login failed. Please try again.']);
        }
    }
}