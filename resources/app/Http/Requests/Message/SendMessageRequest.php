<?php

declare(strict_types=1);

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // MessagePolicy checked in controller
    }

    public function rules(): array
    {
        return [
            'content'       => ['required_unless:type,system', 'nullable', 'string', 'max:4000'],
            'type'          => ['required', 'string', 'in:text,image,file,system'],
            'attachments'   => ['sometimes', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
            'parent_id'     => ['sometimes', 'nullable', 'integer', 'exists:messages,id'],
        ];
    }
}
