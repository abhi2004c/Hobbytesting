<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Enums\PostType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // GroupPolicy checked in controller
    }

    public function rules(): array
    {
        return [
            'group_id'            => ['required', 'integer', 'exists:groups,id'],
            'content'             => ['required_unless:type,poll', 'nullable', 'string', 'max:5000'],
            'type'                => ['required', Rule::in(array_column(PostType::cases(), 'value'))],
            'poll.question'       => ['required_if:type,poll', 'nullable', 'string', 'max:500'],
            'poll.options'        => ['required_if:type,poll', 'nullable', 'array', 'min:2', 'max:10'],
            'poll.options.*'      => ['string', 'max:200'],
            'poll.allow_multiple' => ['sometimes', 'boolean'],
            'poll.ends_at'        => ['sometimes', 'nullable', 'date', 'after:now'],
            'is_announcement'     => ['sometimes', 'boolean'],
            'media'               => ['sometimes', 'array', 'max:10'],
            'media.*'             => ['file', 'max:10240', 'mimes:jpeg,png,gif,webp,mp4,mov'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $group = \App\Models\Group::find($this->input('group_id'));
            if ($group && ! $group->isMember($this->user())) {
                $validator->errors()->add('group_id', 'You must be a member of this group.');
            }
            if ($this->boolean('is_announcement') && $group && ! $group->isAdmin($this->user())) {
                $validator->errors()->add('is_announcement', 'Only admins can create announcements.');
            }
        });
    }
}
