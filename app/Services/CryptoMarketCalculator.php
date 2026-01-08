<?php

namespace App\Services;

use App\DTOs\SymbolMargin;
use App\DTOs\SymbolPriceContainer;

class CryptoMarketCalculator
{

    public function calculateMargin(SymbolPriceContainer $data): SymbolMargin
    {
        [$low, $high] = $data->getExtremes();

        return new SymbolMargin(
            $data->symbol,
            $high->price - $low->price,
            $high->price / $low->price - 1,
            $low,
            $high
        );
    }

}
