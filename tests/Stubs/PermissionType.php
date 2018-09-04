<?php

namespace Kyslik\LaravelFilterable\Test\Stubs;

use Kyslik\LaravelFilterable\Filter;

class PermissionType extends Filter
{

    protected $filterables = [
        'id',
        'permission_id',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
        'active',
        'published',
    ];


    function filterMap(): array
    {
        return [];
    }
}
