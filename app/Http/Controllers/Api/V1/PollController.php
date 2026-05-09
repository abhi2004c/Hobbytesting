<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Feed\Services\PollService;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\Poll;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends BaseApiController
{
    public function __construct(
        private readonly PollService $polls,
    ) {}

    public function vote(Request $request, Poll $poll): JsonResponse
    {
        $validated = $request->validate([
            'option_ids'   => 'required|array|min:1',
            'option_ids.*' => 'integer|exists:poll_options,id',
        ]);

        $this->polls->vote($poll, $request->user(), $validated['option_ids']);

        return $this->successResponse([
            'results' => $this->polls->getResults($poll),
        ], 'Vote recorded.');
    }
}
