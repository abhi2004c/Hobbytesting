<?php

declare(strict_types=1);

namespace App\Domain\Event\Exceptions;

use DomainException;
use Throwable;

class EventCancelledException extends DomainException
{
    public function __construct(string $message = 'This event has been cancelled.', int $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}