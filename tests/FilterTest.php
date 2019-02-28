<?php

namespace Kyslik\LaravelFilterable\Test;

use Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException;
use Kyslik\LaravelFilterable\Test\Stubs\Filter;

class FilterTest extends TestCase
{

    function test_appendable_defaults_returns_correct_array()
    {
        $filter = $this->buildFilter(Filter::class);

        $expected = ['new' => '', 'active' => '', 'scheduled' => ''];
        $this->assertEquals($expected, $filter->appendableDefaults(['active', 'new', 'scheduled']));
    }


    function test_available_filters()
    {
        $filter = $this->buildFilter(Filter::class);
        $this->assertEquals(['active', 'new', 'scheduled', 'random', 'fake', 'disabled'], $filter->availableFilters());
    }


    function test_filter_is_applied_once()
    {
        $filter = $this->buildFilter(Filter::class, 'new&scheduled');
        $this->assertEquals('select * where "recent" = \'1\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
    }


    function test_filter_defined_but_not_implemented()
    {
        $this->expectException(InvalidArgumentException::class);

        $filter = $this->buildFilter(Filter::class, 'active');
        $filter->apply($this->builder);
    }
}
