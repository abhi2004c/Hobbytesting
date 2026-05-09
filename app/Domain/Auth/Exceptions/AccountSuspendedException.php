<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

class AccountSuspendedException extends \Exception
{
    protected $code = 403;
}