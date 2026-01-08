<?php

namespace App\DTOs;

readonly class SymbolMargin
{

    public function __construct(
        public string $symbol,
        public float $absolute,
        public float $relative,
        public SymbolPrice $low,
        public SymbolPrice $high
    ) {
    }

}
