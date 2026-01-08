<?php

namespace App\Services\API;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class APIContainer implements IteratorAggregate
{

    public function __construct(private readonly array $params = []) {
    }

    /**
     * @return Traversable<CryptoMarketAPI>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->params);
    }

    public function getPlatformCount(): int
    {
        return count($this->params);
    }

}
