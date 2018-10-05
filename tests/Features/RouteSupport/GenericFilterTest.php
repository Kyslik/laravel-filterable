<?php

namespace Kyslik\LaravelFilterable\Test\Features\RouteSupport;

use Illuminate\Support\Arr;
use Kyslik\LaravelFilterable\Test\GenericTestCase;
use Kyslik\LaravelFilterable\Test\Stubs\GenericFilter;

class GenericFilterTest extends GenericTestCase
{

    function test_toggle_on()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, 'page=1');

        $this->assertEquals('http://test.dev/?page=1&filter-id=1', $support->toggle(['filter-id' => '1']));
    }


    function test_toggle_off()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, 'page=1&filter-id=1');

        $this->assertEquals('http://test.dev/?page=1', $support->toggle(['filter-id']));
    }


    function test_toggle_on_off_multiple_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support =
            $this->getSupportClass(GenericFilter::class, Arr::query(['page' => 1, 'filter-id' => '!=2', 'filter-name' => 'neo']));

        $this->assertEquals('http://test.dev/?'.Arr::query(['page' => 1, 'filter-id' => '!=2', 'filter-created_at' => 'now']),
            $support->toggle(['filter-name', 'filter-created_at' => 'now']));
    }


    function test_truncate_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support =
            $this->getSupportClass(GenericFilter::class, Arr::query(['page' => 1, 'filter-id' => '!=2', 'filter-name' => 'neo']));

        $this->assertEquals('http://test.dev/?page=1', $support->truncate());
    }


    function test_remove_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class,
            Arr::query(['page' => 1, 'filter-id' => '!=2', 'filter-name' => 'neo', 'name' => '']));

        $this->assertEquals('http://test.dev/?page=1&filter-name=neo', $support->remove(['filter-id', 'name']));
    }


    function test_add_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, Arr::query(['page' => 1, 'filter-id' => '!=2']));

        $expectedQuery = Arr::query(['page' => 1, 'filter-id' => '!=2', 'name' => 'anderson', 'filter-name' => 'neo']);

        $this->assertEquals('http://test.dev/?'.$expectedQuery, $support->add(['name' => 'anderson', 'filter-name' => 'neo']));
    }


    function test_merge_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, Arr::query(['page' => 1, 'filter-id' => '!=2']));

        $this->assertEquals('http://test.dev/?'.Arr::query(['page' => 1, 'filter-id' => '!=3', 'name' => 'anderson']),
            $support->merge(['name' => 'anderson', 'filter-id' => '!=3']));
    }


    function test_has_any_returns_true()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, 'page=1&filter-id=5&name');

        $this->assertTrue($support->hasAny(['filter-created_at', 'name']));
    }


    function test_has_any_returns_false()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, 'page=1&filter-created_at=now');

        $this->assertFalse($support->hasAny(['filter-id', 'filter-name']));
    }


    function test_has_all_returns_true()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, 'page=1&filter-id=2&name=anderson');

        $this->assertTrue($support->hasAll(['name', 'filter-id']));
    }


    function test_has_all_returns_false()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, 'page=1&filter-name=neo&disabled');

        $this->assertFalse($support->hasAll(['filter-id', 'filter-name']));
    }


    function test_has_return_true()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, 'page=1&filter-id=5&disabled');

        $this->assertTrue($support->has('filter-id'));
    }

    function test_has_return_false()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(GenericFilter::class, 'page=1&disabled');

        $this->assertFalse($support->has('filter-id'));
    }
}