<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Support\ServiceProvider;

class FilterableServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/filterable.php' => config_path('filterable.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands(FilterMakeCommand::class);
        }
    }


    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filterable.php', 'filterable');
    }
}
