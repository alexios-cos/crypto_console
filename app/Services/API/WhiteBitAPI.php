<?php

namespace App\Services\API;

use App\Config\CryptoMarketConfig;
use App\DTOs\SymbolPrice;
use App\Exceptions\CurrencyPairNotExist;
use Exception;
use Illuminate\Support\Facades\Http;

class WhiteBitAPI implements CryptoMarketAPI
{

    public function getSpotPrice(string $baseAssetCode, string $quoteAssetCode): SymbolPrice
    {
        $data = $this->getAllPrices();

        $symbolKey = sprintf("%s_%s", $baseAssetCode, $quoteAssetCode);
        $info = $data[$symbolKey] ?? null;

        if (!$info) {
            if (count($data) > 0) {
                throw new CurrencyPairNotExist(CryptoMarketConfig::WHITE_BIT_API_NAME, $baseAssetCode, $quoteAssetCode);
            }

            throw new Exception('Undefined error');
        }

        return $this->dataToPrice($symbolKey, $info, $baseAssetCode, $quoteAssetCode);
    }

    public function getSpotPriceList(): array
    {
        $data = $this->getAllPrices();

        $result = [];
        foreach ($data as $symbol => $item) {
            $price = $this->dataToPrice($symbol, $item);

            if (!$price->price) {
                continue;
            }

            $result[$price->symbol] = $price;
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    private function getAllPrices(): array
    {
        $marketActivityEndpoint = 'https://whitebit.com/api/v4/public/ticker';

        $response = Http::get($marketActivityEndpoint);

        if ($response->failed()) {
            throw new Exception($response->body());
        }

        return $response->json();
    }

    private function dataToPrice(string $symbol, array $data, ?string $baseAssetCode = null, ?string $quoteAssetCode = null): SymbolPrice
    {
        return new SymbolPrice(
            CryptoMarketConfig::WHITE_BIT_API_NAME,
            str_replace('_', '', $symbol),
            (float) $data['last_price'],
            $baseAssetCode,
            $quoteAssetCode,
        );
    }

}
