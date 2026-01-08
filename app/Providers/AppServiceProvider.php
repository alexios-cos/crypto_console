<?php

namespace App\Providers;

use App\Services\API\APIContainer;
use App\Services\API\BinanceAPI;
use App\Services\API\ByBitAPI;
use App\Services\API\JuAPI;
use App\Services\API\PoloniexAPI;
use App\Services\API\WhiteBitAPI;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(APIContainer::class, fn() => new APIContainer([
            new BinanceAPI(),
            new JuAPI(),
            new PoloniexAPI(),
            new ByBitAPI(),
            new WhiteBitAPI(),
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
