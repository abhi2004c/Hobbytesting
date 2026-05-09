<?php

declare(strict_types=1);

namespace App\Domain\Feed\Exceptions;

use DomainException;

class CommentDepthExceededException extends DomainException
{
    public function __construct(string $message = 'Maximum reply depth reached.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}