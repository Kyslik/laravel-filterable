<?php

namespace Kyslik\LaravelFilterable\Test\Features\RouteSupport;

use Illuminate\Http\Request;
use Kyslik\LaravelFilterable\Test\Stubs\Filter;
use Kyslik\LaravelFilterable\Test\TestCase;

class FilterTest extends TestCase
{

    function test_toggle_returns_unmodified_url_if_invalid_argument_passed_in()
    {
        $support = $this->getSupportClass(Filter::class, 'page=1');

        $expected = 'http://test.dev/?page=1';
        $this->assertEquals($expected, $support->toggle('1'));
        $this->assertEquals($expected, $support->toggle(1));
        $this->assertEquals($expected, $support->toggle($this));
    }


    function test_pure_toggle_on()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class);
        $this->assertEquals('http://test.dev/?new', $support->toggle(['new']));
        $this->assertEquals('http://test.dev/?new', $support->toggle(['new']));
    }


    function test_pure_toggle_off()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'new');
        $this->assertEquals('http://test.dev', $support->toggle(['new']));
        $this->assertEquals('http://test.dev', $support->toggle(['new']));
    }

    function test_toggle_in_a_row()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'new');
        $this->assertEquals('http://test.dev', $support->toggle(['new']));
        $this->assertEquals('http://test.dev', $support->toggle(['new']));
        $this->assertEquals('http://test.dev/?fake', $support->toggle(['new', 'fake']));
        $this->assertEquals('http://test.dev/?fake', $support->toggle(['fake', 'new']));
    }


    function test_toggle_on()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&fake=2');

        $this->assertEquals('http://test.dev/?page=1&fake=2&new', $support->toggle(['new']));
        $this->assertEquals('http://test.dev/?page=1&fake=2&new', $support->toggle(['new']));
    }


    function test_toggle_off()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&new=1&fake');

        $this->assertEquals('http://test.dev/?page=1&fake', $support->toggle(['new']));
        $this->assertEquals('http://test.dev/?page=1&fake', $support->toggle(['new']));
    }


    function test_toggle_on_off_multiple_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&fake=2&disabled');

        $this->assertEquals('http://test.dev/?page=1&fake=2&disabled&new=a', $support->toggle(['new' => 'a', 'random']));
        $this->assertEquals('http://test.dev/?page=1&fake=2', $support->toggle(['disabled', 'random']));
    }


    function test_truncate_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&fake=2&disabled');

        $this->assertEquals('http://test.dev/?page=1', $support->truncate());
        $this->assertEquals('http://test.dev/?page=1', $support->truncate());
    }


    function test_remove_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&fake=2&disabled');

        $this->assertEquals('http://test.dev/?page=1&disabled', $support->remove(['fake', 'random']));
        $this->assertEquals('http://test.dev/?page=1&random=5', $support->remove(['fake', 'disabled']));
    }


    function test_add_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5');

        $this->assertEquals('http://test.dev/?page=1&random=5&fake&disabled', $support->add(['random' => '6', 'fake', 'disabled']));
        $this->assertEquals('http://test.dev/?page=1&random=5&disabled', $support->add(['random' => '7', 'disabled']));}


    function test_merge_filters()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5');

        $this->assertEquals('http://test.dev/?page=1&random=6&fake&disabled',
            $support->merge(['random' => '6', 'fake', 'disabled']));

        $this->assertEquals('http://test.dev/?page=1&random=8&disabled',
            $support->merge(['random' => '8', 'disabled']));
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


    function test_has_returns_true()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&random=5&disabled');

        $this->assertTrue($support->has(['random']));
    }


    function test_has_returns_false()
    {
        /** @var \Kyslik\LaravelFilterable\RouteSupport $support */
        $support = $this->getSupportClass(Filter::class, 'page=1&disabled');

        $this->assertFalse($support->has('random'));
    }
}