<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;

interface FilterableContract
{

    function apply(Builder $builder);


    function filterMap();

}