<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ReportStatus;
use Illuminate\Foundation\Http\FormRequest;

class CreateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reportable_type' => ['required', 'string', 'in:post,comment,user,group,message'],
            'reportable_id'   => ['required', 'integer', 'min:1'],
            'reason'          => ['required', 'string', 'in:spam,harassment,hate_speech,violence,misinformation,nudity,other'],
            'description'     => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Map frontend-friendly types to full model class names.
     */
    public function reportableType(): string
    {
        return match ($this->validated('reportable_type')) {
            'post'    => \App\Models\Post::class,
            'comment' => \App\Models\Comment::class,
            'user'    => \App\Models\User::class,
            'group'   => \App\Models\Group::class,
            'message' => \App\Models\Message::class,
            default   => throw new \InvalidArgumentException('Invalid reportable type.'),
        };
    }
}
