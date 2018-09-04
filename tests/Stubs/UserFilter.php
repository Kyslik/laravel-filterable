<?php

namespace Kyslik\LaravelFilterable\Test\Stubs;

use Kyslik\LaravelFilterable\Generic\Filter;

class UserFilter extends Filter
{

    protected $filterables = [
        'id',
        'username',
        'email',
        'created_at',
        'updated_at',
        'deleted_at',
        'active',
        'published',
    ];


    /**Testing helper function.
     *
     * @return string
     */
    public function getGroupingOperator()
    {
        return $this->groupingOperator;
    }
}
