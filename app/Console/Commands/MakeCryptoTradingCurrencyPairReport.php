<?php

namespace App\Console\Commands;

use App\DTOs\SymbolPrice;
use App\DTOs\SymbolPriceContainer;
use App\Services\CryptoMarketService;
use DateTime;
use Illuminate\Console\Command;
use Throwable;

class MakeCryptoTradingCurrencyPairReport extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-crypto-trading-currency-pair-report
                            {base_asset_code : Desired asset}
                            {quote_asset_code : Asset used to price desired asset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Searches for the lowest and highest trade price for said currency pair';

    public function __construct(private readonly CryptoMarketService $cryptoMarketService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws Throwable
     */
    public function handle(): void
    {
        $baseAssetCode = $this->argument('base_asset_code');
        $quoteAssetCode = $this->argument('quote_asset_code');

        $priceContainer = $this->cryptoMarketService->collectPricesForCryptoPair($baseAssetCode, $quoteAssetCode);

        $this->printReport($priceContainer);
    }

    private function printReport(SymbolPriceContainer $priceContainer): void
    {
        $extremes = $priceContainer->getExtremes();
        $time = (new DateTime())->format('Y-m-d H:i:s');

        $this->newLine();
        $this->line("Lowest and highest trade price for currency pair $priceContainer->baseAsset/$priceContainer->quoteAsset at $time");
        $this->newLine();

        $this->table(
            ['Platform', $priceContainer->baseAsset, $priceContainer->quoteAsset],
            array_map(fn(SymbolPrice $p) => [$p->platform, 1, $p->price], $extremes),
        );

        $this->newLine();
    }

}
