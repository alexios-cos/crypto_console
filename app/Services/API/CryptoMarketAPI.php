<?php

namespace App\Services\API;

use App\DTOs\SymbolPrice;
use App\Exceptions\CurrencyPairNotExist;
use Exception;

interface CryptoMarketAPI
{

    /**
     * @throws CurrencyPairNotExist
     * @throws Exception
     */
    public function getSpotPrice(string $baseAssetCode, string $quoteAssetCode): SymbolPrice;

    /**
     * @return SymbolPrice[]
     * @throws Exception
     */
    public function getSpotPriceList(): array;

}
