<?php

declare(strict_types=1);

namespace App\Domain\Group\Exceptions;

class MembershipLimitExceededException extends \Exception
{
    protected $code = 422;
}