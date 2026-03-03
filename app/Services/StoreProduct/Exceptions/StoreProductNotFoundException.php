<?php

namespace App\Services\StoreProduct\Exceptions;

class StoreProductNotFoundException extends \Exception
{
    protected $message = 'Store product not found';
}
