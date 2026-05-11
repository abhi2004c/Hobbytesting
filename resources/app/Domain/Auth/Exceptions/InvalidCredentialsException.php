<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

class InvalidCredentialsException extends \Exception
{
    protected $code = 401;
}