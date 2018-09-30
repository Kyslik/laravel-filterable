<?php

namespace Kyslik\LaravelFilterable\Test;

use Illuminate\Http\Request;
use Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException;

class RequestMacrosTest extends TestCase
{

    function test_has_any_filter_returns_true()
    {
        $filter = $this->buildFilter(RequestMacroFilter::class, 'active&new');
        $this->assertTrue(resolve(Request::class)->hasAnyFilter($filter));
    }


    function test_has_any_filter_returns_false()
    {
        $filter = $this->buildFilter(RequestMacroFilter::class, 'page=1');
        $this->assertFalse(resolve(Request::class)->hasAnyFilter($filter));
    }


    function test_has_any_filter_determines_the_filter_and_returns_true()
    {
        $filter = $this->buildFilter(MacroCallingFilter::class, 'active&new');
        $this->assertTrue($filter->callHasAnyFilter());
    }


    function test_has_any_filter_determines_the_filter_and_returns_false()
    {
        $filter = $this->buildFilter(MacroCallingFilter::class, 'page=1');
        $this->assertFalse($filter->callHasAnyFilter());
    }


    function test_has_any_filter_throws_up_when_filter_is_not_provided()
    {
        $this->expectException(InvalidArgumentException::class);
        resolve(Request::class)->hasAnyFilter();
    }
}

class RequestMacroFilter extends \Kyslik\LaravelFilterable\Filter
{

    /**
     * @return array ex: ['method-name', 'another-method' => 'alias', 'yet-another-method' => ['alias-one', 'alias-two]]
     */
    function filterMap(): array
    {
        return ['active' => 'active', 'recent' => ['new', 'scheduled']];
    }


    function active()
    {
        return $this->builder;
    }


    function recent()
    {
        return $this->builder;
    }

}

class MacroCallingFilter extends RequestMacroFilter
{

    public function callHasAnyFilter()
    {
        return $this->request->hasAnyFilter();
    }
}