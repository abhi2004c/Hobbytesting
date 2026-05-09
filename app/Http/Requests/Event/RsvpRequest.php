<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Enums\RsvpStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('rsvp', $this->route('event')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(RsvpStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}