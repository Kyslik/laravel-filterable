<?php

namespace Kyslik\LaravelFilterable\Test;

use Kyslik\LaravelFilterable\Test\Stubs\Filter;

class RouteSupportTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }


    function test_toggle_returns_unmodified_url_if_invalid_argument_passed_in()
    {
        $support = $this->getSupportClass(Filter::class, 'page=1');

        $expected = 'http://test.dev/?page=1';
        $this->assertEquals($expected, $support->toggle('1'));
        $this->assertEquals($expected, $support->toggle(1));
        $this->assertEquals($expected, $support->toggle($this));
    }


    function test_toggle_on()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1');

        $this->assertEquals('http://test.dev/?page=1&new', $support->toggle(['new']));
    }


    function test_toggle_off()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&new');

        $this->assertEquals('http://test.dev/?page=1', $support->toggle(['new']));
    }


    function test_toggle_on_off_multiple_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&fake=2&disabled');

        $this->assertEquals('http://test.dev/?page=1&fake=2&disabled&new=a', $support->toggle(['new' => 'a', 'random']));
    }


    function test_truncate_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&fake=2&disabled');

        $this->assertEquals('http://test.dev/?page=1', $support->truncate());
    }


    function test_remove_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&fake=2&disabled');

        $this->assertEquals('http://test.dev/?page=1&disabled', $support->remove(['fake', 'random']));
    }


    function test_add_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5');

        $this->assertEquals('http://test.dev/?page=1&random=5&fake&disabled', $support->add(['random' => '6', 'fake', 'disabled']));
    }


    function test_merge_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5');

        $this->assertEquals('http://test.dev/?page=1&random=6&fake&disabled',
            $support->merge(['random' => '6', 'fake', 'disabled']));
    }


    function test_has_any_returns_true()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&disabled');

        $this->assertTrue($support->hasAny(['random' => '6', 'fake', 'disabled']));
    }


    function test_has_any_returns_false()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&disabled');

        $this->assertFalse($support->hasAny(['fake', 'new']));
    }


    function test_has_all_returns_true()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&disabled');

        $this->assertTrue($support->hasAll(['random', 'disabled']));
    }


    function test_has_all_returns_false()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&disabled');

        $this->assertFalse($support->hasAll(['random', 'fake']));
    }


    function test_has()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&disabled');

        $this->assertTrue($support->has(['random']));
    }


    private function getSupportClass(string $filter, $requestQuery = '')
    {
        $filter = $this->buildFilter($filter, $requestQuery);

        return $filter->routeSupport();
    }
}