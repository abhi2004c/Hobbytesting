<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = Group::query()->findOrFail($this->input('group_id'));

        return $this->user()?->can('createEvents', $group) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'type' => ['required', Rule::enum(EventType::class)],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'status' => ['nullable', Rule::enum(EventStatus::class)],

            'location' => [
                Rule::requiredIf(in_array($type, [EventType::InPerson->value, EventType::Hybrid->value], true)),
                'nullable',
                'string',
                'max:255',
            ],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'online_url' => [
                Rule::requiredIf(in_array($type, [EventType::Online->value, EventType::Hybrid->value], true)),
                'nullable',
                'url',
                'max:500',
            ],

            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', 'string', 'max:500'],

            'cover' => ['nullable', 'image', 'max:5120'], // 5 MB
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'starts_at.after' => 'The event start time must be in the future.',
            'ends_at.after' => 'The event end time must be after the start time.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();
        $data['creator_id'] = $this->user()->id;

        return $data;
    }
}