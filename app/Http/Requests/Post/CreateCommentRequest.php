<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'   => ['required', 'string', 'max:1000'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:comments,id'],
        ];
    }
}
