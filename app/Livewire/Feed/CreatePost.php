<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Domain\Feed\DTOs\CreatePostDTO;
use App\Domain\Feed\Services\PostService;
use App\Enums\PostType;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreatePost extends Component
{
    use WithFileUploads;

    public int $groupId;
    public string $content = '';
    public string $type = 'text';
    public string $pollQuestion = '';
    public array $pollOptions = ['', ''];
    public bool $allowMultiple = false;
    public array $media = [];

    public function addPollOption(): void
    {
        if (count($this->pollOptions) < 10) {
            $this->pollOptions[] = '';
        }
    }

    public function removePollOption(int $index): void
    {
        if (count($this->pollOptions) > 2) {
            unset($this->pollOptions[$index]);
            $this->pollOptions = array_values($this->pollOptions);
        }
    }

    public function submit(PostService $service): void
    {
        $this->validate([
            'content'        => $this->type === 'poll' ? 'nullable|string|max:5000' : 'required|string|max:5000',
            'type'           => 'required|in:' . implode(',', array_column(PostType::cases(), 'value')),
            'pollQuestion'   => $this->type === 'poll' ? 'required|string|max:500' : 'nullable',
            'pollOptions.*'  => $this->type === 'poll' ? 'required|string|max:200' : 'nullable',
        ]);

        $user  = auth()->user();

        $dto = CreatePostDTO::fromRequest([
            'group_id'      => $this->groupId,
            'user_id'       => $user->id,
            'content'       => $this->content,
            'type'          => $this->type,
            'poll_question'  => $this->pollQuestion ?: null,
            'poll_options'   => array_filter($this->pollOptions),
            'poll_allow_multiple' => $this->allowMultiple,
        ]);

        $service->create($dto);

        $this->reset(['content', 'type', 'pollQuestion', 'pollOptions', 'allowMultiple', 'media']);
        $this->pollOptions = ['', ''];

        $this->dispatch('post-created');
    }

    public function render()
    {
        return view('livewire.feed.create-post');
    }
}
