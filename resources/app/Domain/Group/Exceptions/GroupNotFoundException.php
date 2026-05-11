<?php

declare(strict_types=1);

namespace App\Domain\Group\Exceptions;

class GroupNotFoundException extends \Exception
{
    protected $code = 404;
}