<?php

declare(strict_types=1);

namespace App\Domain\Feed\Exceptions;

use DomainException;

class PollClosedException extends DomainException
{
    public function __construct(string $message = 'This poll is closed.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}