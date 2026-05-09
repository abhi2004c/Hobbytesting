<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Domain\Feed\Services\PollService;
use App\Models\Poll;
use Livewire\Component;

class PollWidget extends Component
{
    public int $pollId;
    public array $selectedOptions = [];
    public bool $hasVoted = false;

    public function mount(int $pollId, PollService $service): void
    {
        $this->pollId = $pollId;
        $poll = Poll::findOrFail($pollId);
        $this->hasVoted = $service->hasVoted($poll, auth()->user());

        if ($this->hasVoted) {
            $this->selectedOptions = $service->getUserVotes($poll, auth()->user())
                ->pluck('poll_option_id')
                ->toArray();
        }
    }

    public function vote(PollService $service): void
    {
        $poll = Poll::findOrFail($this->pollId);

        throw_if($poll->isExpired(), \RuntimeException::class, 'This poll has expired.');

        $this->validate([
            'selectedOptions'   => 'required|array|min:1',
            'selectedOptions.*' => 'integer|exists:poll_options,id',
        ]);

        $service->vote($poll, auth()->user(), $this->selectedOptions);
        $this->hasVoted = true;
    }

    public function render(PollService $service)
    {
        $poll    = Poll::with('options')->findOrFail($this->pollId);
        $results = $service->getResults($poll);

        return view('livewire.feed.poll-widget', [
            'poll'    => $poll,
            'results' => $results,
        ]);
    }
}
