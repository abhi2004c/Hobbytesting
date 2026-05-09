<?php

declare(strict_types=1);

namespace App\Domain\Feed\Services;

use App\Domain\Feed\Exceptions\AlreadyVotedException;
use App\Domain\Feed\Exceptions\PollClosedException;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PollService
{
    /**
     * @param  array<int, int>  $optionIds
     * @return array<int, PollVote>
     */
    public function vote(Poll $poll, User $user, array $optionIds): array
    {
        return DB::transaction(function () use ($poll, $user, $optionIds): array {
            throw_if($poll->isClosed(), PollClosedException::class);

            throw_if(
                empty($optionIds),
                \InvalidArgumentException::class,
                'At least one option must be selected.'
            );

            throw_if(
                ! $poll->allow_multiple && count($optionIds) > 1,
                \InvalidArgumentException::class,
                'This poll allows only a single selection.'
            );

            throw_if(
                $poll->hasVoted($user),
                AlreadyVotedException::class
            );

            // Verify all options belong to this poll
            $valid = PollOption::query()
                ->where('poll_id', $poll->id)
                ->whereIn('id', $optionIds)
                ->pluck('id')
                ->all();

            throw_if(
                count($valid) !== count($optionIds),
                \InvalidArgumentException::class,
                'One or more invalid options.'
            );

            $votes = [];
            foreach ($valid as $optionId) {
                /** @var PollVote $vote */
                $vote = PollVote::query()->create([
                    'poll_id' => $poll->id,
                    'poll_option_id' => $optionId,
                    'user_id' => $user->id,
                ]);

                PollOption::query()->where('id', $optionId)->increment('votes_count');
                $votes[] = $vote;
            }

            return $votes;
        });
    }

    /**
     * @return array<int, array{id: int, text: string, votes: int, percentage: float}>
     */
    public function getResults(Poll $poll): array
    {
        $total = $poll->totalVotes();

        return $poll->options->map(fn (PollOption $opt): array => [
            'id' => $opt->id,
            'text' => $opt->text,
            'votes' => $opt->votes_count,
            'percentage' => $opt->percentage($total),
        ])->all();
    }

    public function hasVoted(Poll $poll, User $user): bool
    {
        return $poll->hasVoted($user);
    }

    /**
     * @return array<int, int>  IDs of options the user voted for
     */
    public function getUserVotes(Poll $poll, User $user): array
    {
        return PollVote::query()
            ->where('poll_id', $poll->id)
            ->where('user_id', $user->id)
            ->pluck('poll_option_id')
            ->all();
    }
}