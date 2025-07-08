<?php

namespace Anil\Hbl\Providers;

use Anil\Hbl\Facades\Hbl;
use Illuminate\Support\ServiceProvider;

class HblProvider extends ServiceProvider
{
    public function register()
    {
        $this->bindHbl();
        $this->mergeConfig();
    }

    public function boot()
    {
        $this->offerPublishing();
    }

    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/hbl.php' => config_path('hbl.php'),
            ], 'hbl-config');
        }
    }

    protected function bindHbl()
    {
        $this->app->bind('hbl', fn () => new Hbl);
    }

    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/hbl.php', 'hbl');
    }
}
