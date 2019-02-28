<?php

namespace Kyslik\LaravelFilterable\Test;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kyslik\LaravelFilterable\FilterableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{

    /** @var Builder $filter */
    protected $builder;


    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            FilterableServiceProvider::class,
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        /** @var Builder $builder */
        $this->builder = resolve(Builder::class);
    }


    protected function resetBuilder()
    {
        $this->builder = resolve(Builder::class);
    }


    protected function buildFilter(string $filter, $requestQuery = '')
    {
        /** @var Request $request */
        $request = resolve(Request::class)->create('http://test.dev?'.$requestQuery);
        $this->app->instance(Request::class, $request);

        return new $filter($request);
    }


    protected function getSupportClass(string $filter, $requestQuery = '')
    {
        $filter = $this->buildFilter($filter, $requestQuery);

        return $filter->routeSupport();
    }


    protected function dumpQuery(Builder $builder)
    {
        return vsprintf(str_replace(['?'], ['\'%s\''], $builder->toSql()), $builder->getBindings());
    }
}
