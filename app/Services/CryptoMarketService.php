<?php

namespace App\Services;

use App\DTOs\SymbolPriceContainer;
use App\Exceptions\CurrencyPairNotExist;
use App\Exceptions\InsufficientMarketData;
use App\Services\API\APIContainer;
use Exception;

class CryptoMarketService
{

    public function __construct(private readonly APIContainer $APIContainer)
    {
    }

    /**
     * @throws CurrencyPairNotExist
     * @throws Exception
     */
    public function collectPricesForCryptoPair(string $baseAssetCode, string $quoteAssetCode): SymbolPriceContainer
    {
        $prices = [];

        foreach ($this->APIContainer as $api) {
            $prices[] = $api->getSpotPrice($baseAssetCode, $quoteAssetCode);
        }

        if (count($prices) < $this->APIContainer->getPlatformCount()) {
            throw new InsufficientMarketData();
        }

        return new SymbolPriceContainer("$baseAssetCode$quoteAssetCode", $prices, $baseAssetCode, $quoteAssetCode);
    }

    /**
     * @return SymbolPriceContainer[]
     * @throws Exception
     */
    public function collectPricesForAll(): array
    {
        $symbolList = [];
        $priceList = [];

        foreach ($this->APIContainer as $api) {
            $list = $api->getSpotPriceList();
            $priceList[] = $list;

            // finding the longest list of symbols
            if (count($list) > count($symbolList)) {
                $symbolList = array_keys($list);
            }
        }

        $containers = [];
        foreach ($symbolList as $symbol) {

            $prices = [];
            foreach ($priceList as $list) {
                if (!isset($list[$symbol])) {
                    // symbol isn't available on all platforms - skipping it
                    continue 2;
                }

                $prices[] = $list[$symbol];
            }

            $containers[$symbol] = new SymbolPriceContainer($symbol, $prices);
        }

        return $containers;
    }

}
