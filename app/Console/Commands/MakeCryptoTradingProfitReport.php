<?php

namespace App\Console\Commands;

use App\DTOs\SymbolMargin;
use App\Services\CryptoMarketCalculator;
use App\Services\CryptoMarketService;
use DateTime;
use Exception;
use Illuminate\Console\Command;

class MakeCryptoTradingProfitReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-crypto-trading-profit-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates profit from different crypto platforms price margins for each currency pair';

    public function __construct(
        private readonly CryptoMarketService $cryptoMarketService,
        private readonly CryptoMarketCalculator $cryptoMarketCalculator,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle(): void
    {
        $priceContainers = $this->cryptoMarketService->collectPricesForAll();

        $margins = [];
        foreach ($priceContainers as $priceContainer) {
            $margins[] = $this->cryptoMarketCalculator->calculateMargin($priceContainer);
        }

        usort($margins, fn (SymbolMargin $a, SymbolMargin $b) => $b->relative <=> $a->relative);

        $this->printReport($margins);
    }

    /**
     * @param SymbolMargin[] $margins
     * @return void
     */
    private function printReport(array $margins): void
    {
        $time = (new DateTime())->format('Y-m-d H:i:s');

        $this->newLine();
        $this->line("Profit list for all currency pairs at $time");
        $this->newLine();

        $this->table(
            [
                'Symbol',
                'Highest price platform',
                'Highest price',
                'Lowest price platform',
                'Lowest price',
                'Absolute margin',
                'Profit %'
            ],
            array_map(fn (SymbolMargin $margin) => [
                $margin->symbol,
                $margin->high->platform,
                $margin->high->price,
                $margin->low->platform,
                $margin->low->price,
                $margin->absolute,
                $margin->relative * 100, // convert to percentage
            ], $margins)
        );
    }
}
