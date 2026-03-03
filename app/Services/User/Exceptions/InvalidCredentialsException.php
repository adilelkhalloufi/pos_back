<?php

namespace App\Services\User\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    protected $message = 'Identifiants invalides';
}
