<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class InsufficientMarketData extends Exception
{

    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Insufficient market data: one or more platforms does not have data for currency pair', $code, $previous);
    }

}
