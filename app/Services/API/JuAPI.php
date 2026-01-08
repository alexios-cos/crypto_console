<?php

namespace App\Services\API;

use App\Config\CryptoMarketConfig;
use App\DTOs\SymbolPrice;
use App\Exceptions\CurrencyPairNotExist;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Former jbex.com
 */
class JuAPI implements CryptoMarketAPI
{

    private const PRICE_ENDPOINT = 'https://api.jucoin.com/v1/spot/public/ticker/price';

    public function getSpotPrice(string $baseAssetCode, string $quoteAssetCode): SymbolPrice
    {
        $response = Http::get(self::PRICE_ENDPOINT, [
            'symbol' => sprintf("%s_%s", $baseAssetCode, $quoteAssetCode),
        ]);

        $data = $response->json();
        if ($response->failed()) {
            if (isset($data['code']) && $data['code'] === 500 && $data['msg'] === 'SYMBOL_001') {
                throw new CurrencyPairNotExist(CryptoMarketConfig::JU_API_NAME, $baseAssetCode, $quoteAssetCode);
            }

            throw new Exception($response->body());
        }

        if ($data['code'] !== 200 || !isset($data['data'][0])) {
            throw new Exception($response->body());
        }

        return $this->dataToPrice($data['data'][0], $baseAssetCode, $quoteAssetCode);
    }

    public function getSpotPriceList(): array
    {
        $response = Http::get(self::PRICE_ENDPOINT);

        if ($response->failed()) {
            throw new Exception($response->body());
        }

        $data = $response->json();
        if ($data['code'] !== 200 || !isset($data['data'])) {
            throw new Exception($response->body());
        }

        $result = [];

        foreach ($data['data'] as $item) {
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
            CryptoMarketConfig::JU_API_NAME,
            strtoupper(str_replace('_', '', $data['s'])),
            (float) $data['p'],
            $baseAssetCode,
            $quoteAssetCode,
        );
    }

}
