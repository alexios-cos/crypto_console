<?php

namespace App\DTOs;

readonly class SymbolPrice
{

    public function __construct(
        public string $platform,
        public string $symbol,
        public float $price,
        public ?string $baseAsset = null,
        public ?string $quoteAsset = null,
    ) {
    }

}
