<?php

namespace Kyslik\LaravelFilterable\Test;

use Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException;
use Kyslik\LaravelFilterable\Test\Stubs\GenericFilter;

class DefaultGenericFilterTest extends TestCase
{

    function test_redirect_happens()
    {
        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);
        $filter = $this->buildGenericFilter(GenericFilter::class, 'page=1');

        $filter->default(['filter-id' => '1']);
    }


    function test_redirect_does_not_happen()
    {
        $filter = $this->buildGenericFilter(GenericFilter::class, 'filter-name=~neo');

        $filter->default(['created_at' => now()]);

        $this->assertTrue(true, 'We are testing, that \Illuminate\Http\Exceptions\HttpResponseException is not thrown.');
    }


    function test_applied_prevents_redirect()
    {
        $filter = $this->buildGenericFilter(GenericFilter::class, 'filter-name=~neo');
        $filter->default(['filter-name' => '~tank']);

        $this->assertTrue(true, 'We are testing, that \Illuminate\Http\Exceptions\HttpResponseException is not thrown.');
    }

    function test_appendable_defaults_throws_up()
    {
        $this->expectException(InvalidArgumentException::class);

        $filter = $this->buildGenericFilter(GenericFilter::class);
        $filter->default(['joe']);
    }


    function test_defaults_redirect_with_correct_query()
    {
        $filter = $this->buildGenericFilter(GenericFilter::class);

        try {
            $code = 307;
            $filter->default(['filter-name' => '~neo', 'filter-id' => '!=1'], $code);
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $response = $e->getResponse();

            $this->assertEquals($response->getStatusCode(), $code);
            $this->assertTrue($response->isRedirection());

            $parameters = [];
            parse_str(parse_url($response->headers->get('location'), PHP_URL_QUERY), $parameters);
            $this->assertEquals(['filter-name' => '~neo', 'filter-id' => '!=1'], $parameters);
        }
    }
}
