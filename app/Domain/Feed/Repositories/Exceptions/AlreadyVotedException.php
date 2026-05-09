<?php

declare(strict_types=1);

namespace App\Domain\Feed\Exceptions;

use DomainException;

class AlreadyVotedException extends DomainException
{
    public function __construct(string $message = 'You have already voted on this poll.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}