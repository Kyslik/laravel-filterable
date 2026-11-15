<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

        $this->registerMacros();
    }


    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filterable.php', 'filterable');
    }


    private function registerMacros()
    {
        Request::macro('hasAnyFilter', function (?FilterContract $filter = null) {
            if (is_null($filter)) {
                foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 6) as $frame) {
                    if (($frame['object'] ?? null) instanceof FilterContract) {
                        $filter = $frame['object'];
                        break;
                    }
                }
            }

            if ( ! $filter instanceof FilterContract) {
                throw new InvalidArgumentException('Macro \'->hasAnyFilter\' requires a parameter of a \Kyslik\LaravelFilterable\FilterContract.');
            }

            /** @var Request $this */
            return $this->hasAny($filter->availableFilters());
        });

        Request::macro('fullUrlWithNiceQuery', function (array $query) {
            /** @var Request $this */
            $question = $this->getBaseUrl().$this->getPathInfo() == '/' ? '/?' : '?';
            
            return empty($query) ? $this->url() :
                rtrim(str_replace('=&', '&', $this->url().$question.Arr::query((force_assoc_array($query, '')))), '=');
        });

        if ( ! method_exists(Arr::class, 'query')) {
            Arr::macro('query', function (array $query) {
                return http_build_query($query, '', '&', PHP_QUERY_RFC3986);
            });
        }
    }
}
