<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;

interface FilterContract
{

    /**
     * Applies filters on a $builder instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    function apply(Builder $builder): Builder;


    /**
     * @param array $defaults
     * @param int   $code
     *
     * @throws \Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException
     */
    function default(array $defaults, int $code = 307);


    /**
     * Available filters that we can expect in the query string.
     *
     * @return array
     */
    public function availableFilters(): array;


    /**
     * @return array ex: ['method-name', 'another-method' => 'alias', 'yet-another-method' => ['alias-one', 'alias-two]]
     */
    function filterMap(): array;

}
