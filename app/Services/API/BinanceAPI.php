<?php

namespace App\Services\API;

use App\Config\CryptoMarketConfig;
use App\DTOs\SymbolPrice;
use App\Exceptions\CurrencyPairNotExist;
use Exception;
use Illuminate\Support\Facades\Http;

class BinanceAPI implements CryptoMarketAPI
{

    private const PRICE_ENDPOINT = 'https://api.binance.com/api/v3/ticker/price';

    public function getSpotPrice(string $baseAssetCode, string $quoteAssetCode): SymbolPrice
    {
        $response = Http::get(self::PRICE_ENDPOINT, [
            'symbol' => "$baseAssetCode$quoteAssetCode"
        ]);

        $data = $response->json();
        if ($response->failed()) {
            if (isset($data['code']) && $data['code'] === -1121) {
                throw new CurrencyPairNotExist(CryptoMarketConfig::BINANCE_API_NAME, $baseAssetCode, $quoteAssetCode);
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
        $response = Http::get(self::PRICE_ENDPOINT);

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
            CryptoMarketConfig::BINANCE_API_NAME,
            $data['symbol'],
            (float) $data['price'],
            $baseAssetCode,
            $quoteAssetCode,
        );
    }

}
