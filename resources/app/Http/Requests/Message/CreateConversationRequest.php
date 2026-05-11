<?php

declare(strict_types=1);

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'       => ['required', 'string', 'in:direct,group'],
            'user_ids'   => ['required', 'array', 'min:1', 'max:49'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'name'       => ['required_if:type,group', 'nullable', 'string', 'max:100'],
        ];
    }
}
