<?php

namespace Kyslik\LaravelFilterable\Test;

use Illuminate\Http\Request;
use Kyslik\LaravelFilterable\Generic\Templater;

abstract class GenericTestCase extends TestCase
{

    protected function buildFilter(string $filter, $requestQuery = '')
    {
        /** @var Request $request */
        $request = resolve(Request::class)->create('http://test.dev?'.$requestQuery);
        $this->app->instance(Request::class, $request);

        return new $filter($request, resolve(Templater::class));
    }
}
