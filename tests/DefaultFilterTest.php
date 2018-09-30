<?php

namespace Kyslik\LaravelFilterable\Test;

use Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException;
use Kyslik\LaravelFilterable\Filter;

class DefaultFilterTest extends TestCase
{

    function test_redirect_happens()
    {
        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);
        $filter = $this->buildFilter(DefaultFilter::class, 'page=1');

        $filter->default(['name' => 'neo']);
    }


    function test_redirect_does_not_happen()
    {
        $filter = $this->buildFilter(DefaultFilter::class, 'name=neo&scheduled');

        $filter->default(['scheduled']);

        $this->assertTrue(true, 'We are testing, that \Illuminate\Http\Exceptions\HttpResponseException is not thrown.');
    }


    function test_applied_prevents_redirect()
    {
        $filter = $this->buildFilter(DefaultFilter::class, 'name=neo&scheduled');
        $filter->default(['name' => 'tank']);

        $this->assertTrue(true, 'We are testing, that \Illuminate\Http\Exceptions\HttpResponseException is not thrown.');
    }


    function test_appendable_defaults_throws_up()
    {
        $this->expectException(InvalidArgumentException::class);

        $filter = $this->buildFilter(DefaultFilter::class);
        $filter->default(['joe']);
    }


    function test_defaults_redirect_with_correct_query()
    {
        $filter = $this->buildFilter(DefaultFilter::class);

        try {
            $code = 307;
            $filter->default(['name' => 'neo', 'scheduled'], $code);
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $response = $e->getResponse();

            $this->assertEquals($response->getStatusCode(), $code);
            $this->assertTrue($response->isRedirection());

            $parameters = [];
            parse_str(parse_url($response->headers->get('location'), PHP_URL_QUERY), $parameters);
            $this->assertEquals(['name' => 'neo', 'scheduled' => ''], $parameters);
        }
    }
}

class DefaultFilter extends Filter
{

    function filterMap(): array
    {
        return [
            'name'      => 'name',
            'active'    => ['active', 'valid'],
            'scheduled' => 'scheduled',
        ];
    }


    public function name()
    {
        return $this->builder;
    }


    public function active()
    {
        return $this->builder;
    }


    public function scheduled()
    {
        return $this->builder;
    }
}
