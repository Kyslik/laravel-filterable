<?php

namespace Kyslik\LaravelFilterable\Test;

use Kyslik\LaravelFilterable\GenericFilterable;

class UserFilter extends GenericFilterable
{
    protected $filterables = ['id', 'username', 'created_at', 'updated_at', 'deleted_at', 'active', 'published'];

}