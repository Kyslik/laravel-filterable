<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;

trait FilterableTrait
{

    /**
     * @param \Illuminate\Database\Eloquent\Builder        $query
     * @param \Kyslik\LaravelFilterable\FilterableContract $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter(Builder $query, FilterableContract $filters)
    {
        return $filters->apply($query);
    }
}