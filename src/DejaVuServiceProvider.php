<?php

namespace MarufMax\DejaVu;

use Illuminate\Support\ServiceProvider;

class DejaVuServiceProvider extends ServiceProvider
{
    /**
     * Booting up the package
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }
    }

    /**
     * Publishing package related files
     *
     * @return void
     */
    protected function registerPublishing() : void
    {
        $this->publishes([
            __DIR__ . '/../config/dejavu.php' => config('dejavu.php')
        ], 'dejavu-config');
    }
}