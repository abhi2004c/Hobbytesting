<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Group\Services\InvitationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitations) {}

    public function accept(Request $request, string $token): RedirectResponse
    {
        $this->invitations->acceptInvitation($token, $request->user());

        return redirect()->route('groups.index')->with('success', 'You have joined the group!');
    }

    public function decline(string $token): RedirectResponse
    {
        $this->invitations->declineInvitation($token);

        return redirect()->route('home')->with('success', 'Invitation declined.');
    }
}
