<?php

declare(strict_types=1);

namespace App\Domain\Event\Exceptions;

use DomainException;
use Throwable;

class EventCapacityExceededException extends DomainException
{
    public function __construct(string $message = 'Event has reached maximum capacity.', int $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}