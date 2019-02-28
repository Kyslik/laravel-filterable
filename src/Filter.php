<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException;
use Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance;

abstract class Filter implements FilterContract
{

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $filterMap;


    public function __construct(Request $request)
    {
        $this->request   = $request;
        $this->filterMap = $this->filterMap();
    }


    public function routeSupport(): RouteSupport
    {
        return app()->makeWith(RouteSupport::class, ['request' => $this->request, 'filter' => $this]);
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Exception
     */
    public function apply(Builder $builder): Builder
    {
        return $this->setBuilder($builder)->applyFilters()->getBuilder();
    }


    /**
     * @inheritdoc
     */
    public function availableFilters(): array
    {
        return Arr::flatten($this->filterMap);
    }


    /**
     * @param array $defaults
     * @param int   $code
     *
     * @throws \Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException
     */
    public function default(array $defaults, int $code = 307)
    {
        if ($this->request->isMethod('GET') && ! empty($defaults) && ! $this->request->hasAnyFilter()) {
            $appends = $this->appendableDefaults($defaults);

            if ( ! empty($appends)) {
                abort(redirect($this->request->fullUrlWithNiceQuery($appends), $code));
            }
        }
    }


    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return Filter
     */
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }


    /**
     * @param array $defaults
     *
     * @return array
     * @throws \Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException
     */
    protected function appendableDefaults(array $defaults): array
    {
        $appends  = [];
        $filters  = $this->availableFilters();
        $defaults = force_assoc_array($defaults, '');

        foreach ($defaults as $filter => $default) {
            if ( ! in_array($filter, $filters)) {
                throw new InvalidArgumentException('Attempting to use default filter \''.$filter.'\', with no effect.');
            }
            $appends[$filter] = $default;
        }

        return $appends;
    }


    /**
     * @throws \Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance
     */
    protected function builderPresent()
    {
        if (empty($this->builder)) {
            throw new MissingBuilderInstance();
        }
    }


    /**
     * @return $this
     * @throws \Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance
     * @throws \Exception
     */
    protected function applyFilters()
    {
        $this->builderPresent();

        foreach ($this->filters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->builder = (is_null($value)) ? $this->$filter() : $this->$filter($value);
                continue;
            }
            throw new InvalidArgumentException('Filter \''.$filter.'\' is declared in \'filterMap\', but it does not exist.');
        }

        return $this;
    }


    private function filters(): array
    {
        if (empty($this->filterMap)) {
            return [];
        }

        foreach ($this->filterMap as $filter => $value) {
            $method = (is_string($filter)) ? $filter : $value;

            // head([]) === false, we check if head returns false and remove that item from array, I am sorry
            if (($filters[$method] = head($this->request->only($value))) === false) {
                unset($filters[$method]);
            }
        }

        return $filters ?? [];
    }
}
