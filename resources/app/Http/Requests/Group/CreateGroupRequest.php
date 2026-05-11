<?php

declare(strict_types=1);

namespace App\Http\Requests\Group;

use App\Enums\GroupPrivacy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:3', 'max:100'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'category_id' => ['required', 'integer', 'exists:group_categories,id'],
            'privacy'     => ['required', Rule::in(array_column(GroupPrivacy::cases(), 'value'))],
            'location'    => ['nullable', 'string', 'max:200'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'max_members' => ['nullable', 'integer', 'min:2', 'max:' . config('community.limits.max_group_members_premium')],
            'cover'       => ['nullable', 'image', 'max:4096'],
            'settings'    => ['sometimes', 'array'],
        ];
    }
}