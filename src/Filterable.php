<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{

    /**
     * @param \Illuminate\Database\Eloquent\Builder    $query
     * @param \Kyslik\LaravelFilterable\FilterContract $filter
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter(Builder $query, FilterContract $filter)
    {
        return $filter->apply($query);
    }
}
