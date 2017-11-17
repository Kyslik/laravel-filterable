<?php

namespace Kyslik\LaravelFilterable\Test;

use Carbon\Carbon;

class GenericFilterTest extends TestCase
{

    function test_generic_filter_equals()
    {
        $filter = $this->buildFilter('filter-username=joe');
        $this->assertEquals('select * where "username" = \'joe\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_not_equals()
    {
        $filter = $this->buildFilter('filter-username=!=joe');
        $this->assertEquals('select * where "username" != \'joe\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_less_than()
    {
        $filter = $this->buildFilter('filter-username=<joe');
        $this->assertEquals('select * where "username" < \'joe\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_greater_than()
    {
        $filter = $this->buildFilter('filter-username=>joe');
        $this->assertEquals('select * where "username" > \'joe\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_equals_or_less_than()
    {
        $filter = $this->buildFilter('filter-username=<=joe');
        $this->assertEquals('select * where "username" <= \'joe\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_equals_or_greater_than()
    {
        $filter = $this->buildFilter('filter-username=>=joe');
        $this->assertEquals('select * where "username" >= \'joe\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_like()
    {
        $filter = $this->buildFilter('filter-username=~joe');
        $this->assertEquals('select * where "username" like \'%joe%\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_not_like()
    {
        $filter = $this->buildFilter('filter-username=!~joe');
        $this->assertEquals('select * where "username" not like \'%joe%\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_where_between()
    {
        $filter = $this->buildFilter('filter-username=><joe,jane');
        $this->assertEquals('select * where "username" between \'jane\' and \'joe\'',
            $this->dumpQuery($filter->apply($this->builder)));

        $this->resetBuilder();

        $filter = $this->buildFilter('filter-username=><jane,joe');
        $this->assertEquals('select * where "username" between \'jane\' and \'joe\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_where_not_between()
    {
        $filter = $this->buildFilter('filter-username=!><joe,jane');
        $this->assertEquals('select * where "username" not between \'jane\' and \'joe\'',
            $this->dumpQuery($filter->apply($this->builder)));

        $this->resetBuilder();

        $filter = $this->buildFilter('filter-username=!><jane,joe');
        $this->assertEquals('select * where "username" not between \'jane\' and \'joe\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_where_in()
    {
        $filter = $this->buildFilter('filter-username=i=jane,joe');
        $this->assertEquals('select * where "username" in (\'jane\', \'joe\')',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_where_not_in()
    {
        $filter = $this->buildFilter('filter-username=i=!jane,joe');
        $this->assertEquals('select * where "username" not in (\'jane\', \'joe\')',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_boolean_equals_true()
    {
        $filter = $this->buildFilter('filter-username=b=1');
        $this->assertEquals('select * where "username" = \'1\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
        $filter = $this->buildFilter('filter-username=b=true');
        $this->assertEquals('select * where "username" = \'1\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
        $filter = $this->buildFilter('filter-username=b=yes');
        $this->assertEquals('select * where "username" = \'1\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_boolean_equals_false()
    {
        $filter = $this->buildFilter('filter-username=b=0');
        $this->assertEquals('select * where "username" = \'0\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
        $filter = $this->buildFilter('filter-username=b=false');
        $this->assertEquals('select * where "username" = \'0\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
        $filter = $this->buildFilter('filter-username=b=no');
        $this->assertEquals('select * where "username" = \'0\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_boolean_equals_not_true()
    {
        $filter = $this->buildFilter('filter-username=b!=1');
        $this->assertEquals('select * where "username" != \'1\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
        $filter = $this->buildFilter('filter-username=b!=true');
        $this->assertEquals('select * where "username" != \'1\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
        $filter = $this->buildFilter('filter-username=b!=yes');
        $this->assertEquals('select * where "username" != \'1\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_boolean_equals_not_false()
    {
        $filter = $this->buildFilter('filter-username=b!=0');
        $this->assertEquals('select * where "username" != \'0\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
        $filter = $this->buildFilter('filter-username=b!=false');
        $this->assertEquals('select * where "username" != \'0\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
        $filter = $this->buildFilter('filter-username=b!=no');
        $this->assertEquals('select * where "username" != \'0\'', $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_timestamp_equals()
    {
        $now    = Carbon::now();
        $filter = $this->buildFilter('filter-created_at=t='.$now->timestamp);
        $this->assertEquals('select * where "created_at" = \''.$now->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_timestamp_not_equals()
    {
        $now    = Carbon::now();
        $filter = $this->buildFilter('filter-created_at=t!='.$now->timestamp);
        $this->assertEquals('select * where "created_at" != \''.$now->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_timestamp_less_than()
    {
        $now    = Carbon::now();
        $filter = $this->buildFilter('filter-created_at=t>'.$now->timestamp);
        $this->assertEquals('select * where "created_at" > \''.$now->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_timestamp_greater_than()
    {
        $now    = Carbon::now();
        $filter = $this->buildFilter('filter-created_at=t<'.$now->timestamp);
        $this->assertEquals('select * where "created_at" < \''.$now->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_timestamp_equals_or_less_than()
    {
        $now    = Carbon::now();
        $filter = $this->buildFilter('filter-created_at=t>='.$now->timestamp);
        $this->assertEquals('select * where "created_at" >= \''.$now->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_timestamp_equals_or_greater_than()
    {
        $now    = Carbon::now();
        $filter = $this->buildFilter('filter-created_at=t<='.$now->timestamp);
        $this->assertEquals('select * where "created_at" <= \''.$now->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_timestamp_between()
    {
        $now = Carbon::now();
        $then = Carbon::now()->addDay();
        $filter = $this->buildFilter('filter-username=t><'.$now->timestamp.','.$then->timestamp);
        $this->assertEquals('select * where "username" between \''.$now->toDateTimeString().'\' and \''.$then->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));

        $this->resetBuilder();

        $filter = $this->buildFilter('filter-username=t><'.$then->timestamp.','.$now->timestamp);
        $this->assertEquals('select * where "username" between \''.$now->toDateTimeString().'\' and \''.$then->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }


    function test_generic_filter_timestamp_not_between()
    {
        $now = Carbon::now();
        $then = Carbon::now()->addDay();
        $filter = $this->buildFilter('filter-username=t!><'.$now->timestamp.','.$then->timestamp);
        $this->assertEquals('select * where "username" not between \''.$now->toDateTimeString().'\' and \''.$then->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));

        $this->resetBuilder();

        $filter = $this->buildFilter('filter-username=t!><'.$then->timestamp.','.$now->timestamp);
        $this->assertEquals('select * where "username" not between \''.$now->toDateTimeString().'\' and \''.$then->toDateTimeString().'\'',
            $this->dumpQuery($filter->apply($this->builder)));
    }

}