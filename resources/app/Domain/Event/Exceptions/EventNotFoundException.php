<?php

declare(strict_types=1);

namespace App\Domain\Event\Exceptions;

use DomainException;
use Throwable;

class EventNotFoundException extends DomainException
{
    public function __construct(string $message = 'Event not found.', int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}