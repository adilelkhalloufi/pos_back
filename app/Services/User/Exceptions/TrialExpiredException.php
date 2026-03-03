<?php

namespace App\Services\User\Exceptions;

use Exception;

class TrialExpiredException extends Exception
{
    protected $message = 'Votre période d\'essai a expiré. Veuillez contacter l\'administrateur du système ou un représentant commercial pour mettre à niveau votre abonnement.';
}
