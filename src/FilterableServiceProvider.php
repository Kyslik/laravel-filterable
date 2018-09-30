<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException;

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

        Request::macro('hasAnyFilter', function (?FilterContract $filter = null) {
            if (is_null($filter)) {
                $filter = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4)[3]['object'] ?? null;
            }

            if ( ! $filter instanceof FilterContract) {
                throw new InvalidArgumentException('Macro \'->hasAnyFilter\' requires a parameter of a \Kyslik\LaravelFilterable\FilterContract.');
            }

            /** @var Request $this */
            return $this->hasAny($filter->availableFilters());
        });
    }


    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filterable.php', 'filterable');
    }
}
