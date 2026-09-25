<?php

namespace App\Exceptions\Auth;

use Exception;

class InactiveAccountException extends Exception
{
    public function __construct(string $message = 'This account is inactive.')
    {
        parent::__construct($message);
    }
}
