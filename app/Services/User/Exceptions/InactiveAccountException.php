<?php

namespace App\Services\User\Exceptions;

use Exception;

class InactiveAccountException extends Exception
{
    protected $message = 'Votre compte est inactif';
}
