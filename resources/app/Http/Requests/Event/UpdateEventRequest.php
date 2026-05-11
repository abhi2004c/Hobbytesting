<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('event')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:3', 'max:150'],
            'description' => ['sometimes', 'string', 'min:10', 'max:5000'],
            'type' => ['sometimes', Rule::enum(EventType::class)],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'status' => ['sometimes', Rule::enum(EventStatus::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'online_url' => ['nullable', 'url', 'max:500'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', 'string', 'max:500'],
            'cover' => ['nullable', 'image', 'max:5120'],
        ];
    }
}