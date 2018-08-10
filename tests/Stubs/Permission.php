<?php

namespace Kyslik\LaravelFilterable\Test\Stubs;

use Kyslik\LaravelFilterable\Filter;

class PermissionFilter extends Filter
{

    protected $filterables = [
        'id',
        'role_id',
        'level',
        'created_at',
        'updated_at',
        'deleted_at',
        'active',
        'published',
    ];

    function filterMap(): array
    {
        return [
        ];
    }

}
