<?php

declare(strict_types=1);

namespace App\Http\Requests\Group;

use App\Enums\GroupPrivacy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('group')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'min:3', 'max:100'],
            'description' => ['sometimes', 'string', 'min:10', 'max:2000'],
            'category_id' => ['sometimes', 'integer', 'exists:group_categories,id'],
            'privacy'     => ['sometimes', Rule::in(array_column(GroupPrivacy::cases(), 'value'))],
            'location'    => ['sometimes', 'nullable', 'string', 'max:200'],
            'latitude'    => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'max_members' => ['sometimes', 'nullable', 'integer', 'min:2'],
            'cover'       => ['sometimes', 'nullable', 'image', 'max:4096'],
            'settings'    => ['sometimes', 'array'],
        ];
    }
}