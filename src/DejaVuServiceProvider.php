<?php

namespace MarufMax\DejaVu;

use Illuminate\Support\ServiceProvider;

class DejaVuServiceProvider extends ServiceProvider
{
    /**
     * Booting up the package
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->registerFacades();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dejavu.php','dejavu'
        );
    }

    /**
     * Publishing package related files
     *
     * @return void
     */
    protected function registerPublishing() : void
    {
        $this->publishes([
            __DIR__ . '/../config/dejavu.php' => config_path('dejavu.php')
        ], 'dejavu-config');
    }

    protected function registerFacades()
    {
        $this->app->singleton('DejaVu', function ($app) {
           return new \MarufMax\DejaVu\RedisCache();
        });
    }
}
