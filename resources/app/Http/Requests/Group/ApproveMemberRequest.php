<?php

declare(strict_types=1);

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class ApproveMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageMembers', $this->route('group')) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'action'  => ['required', 'in:approve,reject'],
        ];
    }
}