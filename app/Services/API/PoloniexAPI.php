<?php

namespace App\Services\API;

use App\Config\CryptoMarketConfig;
use App\DTOs\SymbolPrice;
use App\Exceptions\CurrencyPairNotExist;
use Exception;
use Illuminate\Support\Facades\Http;

class PoloniexAPI implements CryptoMarketAPI
{

    public function getSpotPrice(string $baseAssetCode, string $quoteAssetCode): SymbolPrice
    {
        $symbol = sprintf("%s_%s", $baseAssetCode, $quoteAssetCode);

        $priceEndpoint = "https://api.poloniex.com/markets/$symbol/price";

        $response = Http::get($priceEndpoint);

        $data = $response->json();
        if ($response->failed()) {
            if (isset($data['code']) && $data['code'] === 24101) {
                throw new CurrencyPairNotExist(CryptoMarketConfig::POLONIEX_API_NAME, $baseAssetCode, $quoteAssetCode);
            }

            throw new Exception($response->body());
        }

        if (!isset($data['price'])) {
            throw new Exception($response->body());
        }

        return $this->dataToPrice($data, $baseAssetCode, $quoteAssetCode);
    }

    public function getSpotPriceList(): array
    {
        $priceEndpoint = "https://api.poloniex.com/markets/price";

        $response = Http::get($priceEndpoint);

        if ($response->failed()) {
            throw new Exception($response->body());
        }

        $result = [];
        foreach ($response->json() as $item) {
            $price = $this->dataToPrice($item);

            if (!$price->price) {
                continue;
            }

            $result[$price->symbol] = $price;
        }

        return $result;
    }

    private function dataToPrice(array $data, ?string $baseAssetCode = null, ?string $quoteAssetCode = null): SymbolPrice
    {
        return new SymbolPrice(
            CryptoMarketConfig::POLONIEX_API_NAME,
            str_replace('_', '', $data['symbol']),
            (float) $data['price'],
            $baseAssetCode,
            $quoteAssetCode,
        );
    }

}
