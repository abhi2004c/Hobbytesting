<?php

declare(strict_types=1);

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('inviteMembers', $this->route('group')) ?? false;
    }

    public function rules(): array
    {
        return [
            'email'   => ['required', 'email:rfc'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }
}