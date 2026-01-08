<?php

namespace App\Services\API;

use App\Config\CryptoMarketConfig;
use App\DTOs\SymbolPrice;
use App\Exceptions\CurrencyPairNotExist;
use Exception;
use Illuminate\Support\Facades\Http;

class ByBitAPI implements CryptoMarketAPI
{

    private const PRICE_ENDPOINT = 'https://api.bybit.com/v5/market/tickers';

    public function getSpotPrice(string $baseAssetCode, string $quoteAssetCode): SymbolPrice
    {
        $response = Http::get(self::PRICE_ENDPOINT, [
            'category' => 'spot',
            'symbol' => "$baseAssetCode$quoteAssetCode",
        ]);

        $data = $response->json();
        if ($response->failed()) {
            if (isset($data['retCode']) && $data['retCode'] === 10001) {
                throw new CurrencyPairNotExist(CryptoMarketConfig::BY_BIT_API_NAME, $baseAssetCode, $quoteAssetCode);
            }

            throw new Exception($response->body());
        }


        if ($data['retCode'] !== 0 || !isset($data['result']['list'][0]['lastPrice'])) {
            throw new Exception($response->body());
        }

        return $this->dataToPrice($data['result']['list'][0], $baseAssetCode, $quoteAssetCode);
    }

    public function getSpotPriceList(): array
    {
        $response = Http::get(self::PRICE_ENDPOINT, [
            'category' => 'spot',
        ]);

        if ($response->failed()) {
            throw new Exception($response->body());
        }

        $data = $response->json();
        if ($data['retCode'] !== 0 || !isset($data['result']['list'])) {
            throw new Exception($response->body());
        }

        $result = [];
        foreach ($data['result']['list'] as $item) {
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
            CryptoMarketConfig::BY_BIT_API_NAME,
            $data['symbol'],
            (float) $data['lastPrice'],
            $baseAssetCode,
            $quoteAssetCode,
        );
    }

}
