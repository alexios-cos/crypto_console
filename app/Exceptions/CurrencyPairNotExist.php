<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CurrencyPairNotExist extends Exception
{

    public function __construct(string $platform, string $baseAssetCode, $quoteAssetCode, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Currency pair $baseAssetCode/$quoteAssetCode for $platform does not exist";
        parent::__construct($message, $code, $previous);
    }

}
