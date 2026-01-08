<?php

namespace App\DTOs;

class SymbolPriceContainer
{

    // little bit of cache
    private array $extremes = [];

    /**
     * @param string $symbol
     * @param SymbolPrice[] $prices
     * @param string|null $baseAsset
     * @param string|null $quoteAsset
     */
    public function __construct(
        public readonly string $symbol,
        public readonly array $prices,
        public readonly ?string $baseAsset = null,
        public readonly ?string $quoteAsset = null
    ) {
    }

    /**
     * @return SymbolPrice[]
     */
    public function getExtremes(): array
    {
        if ($this->extremes !== []) {
            return $this->extremes;
        }

        if (!count($this->prices)) {
            return [];
        }

        $currentLowest = null;
        $currentHighest = null;

        foreach ($this->prices as $price) {
            if ($currentHighest === null || $currentLowest === null) {
                $currentLowest = $price;
                $currentHighest = $price;
                continue;
            }

            if ($currentLowest->price > $price->price) {
                $currentLowest = $price;
            }

            if ($currentHighest->price < $price->price) {
                $currentHighest = $price;
            }
        }

        $this->extremes = [$currentLowest, $currentHighest];

        return $this->extremes;
    }

}
