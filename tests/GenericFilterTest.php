<?php

namespace Kyslik\LaravelFilterable\Test;

use Carbon\Carbon;
use Kyslik\LaravelFilterable\Test\Stubs\GenericFilter;
use Kyslik\LaravelFilterable\Test\Stubs\UserFilter;

class GenericFilterTest extends GenericTestCase
{

    private $prefix;


    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->prefix = 'f-';
    }


    public function setUp()
    {
        parent::setUp();
        config()->set('filterable.prefix', $this->prefix);
    }


    function test_appendable_defaults_returns_correct_array()
    {
        $filter = $this->buildFilter(GenericFilter::class);

        $expected = ['name' => '', 'f-id' => '1'];
        $this->assertEquals($expected, $filter->appendableDefaults(['name', 'f-id' => '1']));
    }


    function test_available_filters()
    {
        $filter = $this->buildFilter(GenericFilter::class);
        $this->assertEquals(['f-id', 'f-name', 'f-created_at', 'name'], $filter->availableFilters());
    }


    function test_filter_is_applied_once()
    {
        $filter = $this->buildFilter(GenericFilter::class, 'name=one&name=neo&f-id=1&f-id=2');
        $this->assertEquals('select * where "name" = \'neo\' and "id" = \'2\'', $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
    }


    function test_grouping_operator_is_determined()
    {
        config()->set('filterable.uri_grouping_operator', 'grouping-operator');

        $anonymous = function ($query, $expected, $default) {
            config()->set('filterable.default_grouping_operator', $default);
            $filter = $this->buildFilter(UserFilter::class, 'grouping-operator='.$query);
            $this->assertEquals($filter->getGroupingOperator(), $expected);
        };

        // standard
        $anonymous('or', 'or', 'and');
        $anonymous('and', 'and', 'or');
        // case sensitive
        $anonymous('OR', 'or', 'and');
        $anonymous('AND', 'and', 'or');
        // use default since invalid query
        $anonymous('foo', 'and', 'and');
        $anonymous('bar', 'or', 'or');
    }


    function test_grouping_operator_is_applied()
    {
        config()->set('filterable.default_grouping_operator', 'or');

        $this->assertQuery('select * where "username" = \'joe\' or "email" = \'joe@acme.com\'', [
            $this->prefix.'username' => 'joe',
            $this->prefix.'email'    => 'joe@acme.com',
        ]);

        $this->assertQuery('select * where "username" like \'%joe%\' or "email" = \'joe@acme.com\'', [
            $this->prefix.'username' => '~joe',
            $this->prefix.'email'    => 'joe@acme.com',
        ]);

        $this->assertQuery('select * where "username" between \'jane\' and \'joe\' or "email" = \'joe@acme.com\'', [
            $this->prefix.'username' => '><jane,joe',
            $this->prefix.'email'    => 'joe@acme.com',
        ]);
    }


    function test_generic_filter_equals()
    {
        $this->assertQuery('select * where "username" = \'joe\'', [
            $this->prefix.'username' => 'joe',
        ]);
    }


    function test_generic_filter_not_equals()
    {
        $this->assertQuery('select * where "username" != \'joe\'', [
            $this->prefix.'username' => '!=joe',
        ]);
    }


    function test_generic_filter_less_than()
    {
        $this->assertQuery('select * where "username" < \'joe\'', [
            $this->prefix.'username' => '<joe',
        ]);
    }


    function test_generic_filter_greater_than()
    {
        $this->assertQuery('select * where "username" > \'joe\'', [
            $this->prefix.'username' => '>joe',
        ]);
    }


    function test_generic_filter_equals_or_less_than()
    {
        $this->assertQuery('select * where "username" <= \'joe\'', [
            $this->prefix.'username' => '<=joe',
        ]);
    }


    function test_generic_filter_equals_or_greater_than()
    {
        $this->assertQuery('select * where "username" >= \'joe\'', [
            $this->prefix.'username' => '>=joe',
        ]);
    }


    function test_generic_filter_like()
    {
        $this->assertQuery('select * where "username" like \'%joe%\'', [
            $this->prefix.'username' => '~joe',
        ]);
    }


    function test_generic_filter_not_like()
    {
        $this->assertQuery('select * where "username" not like \'%joe%\'', [
            $this->prefix.'username' => '!~joe',
        ]);
    }


    function test_generic_filter_where_between()
    {
        $this->assertQuery('select * where "username" between \'jane\' and \'joe\'', [
            $this->prefix.'username' => '><joe,jane',
        ]);

        $this->assertQuery('select * where "username" between \'jane\' and \'joe\'', [
            $this->prefix.'username' => '><jane,joe',
        ]);
    }


    function test_generic_filter_where_not_between()
    {
        $this->assertQuery('select * where "username" not between \'jane\' and \'joe\'', [
            $this->prefix.'username' => '!><joe,jane',
        ]);

        $this->assertQuery('select * where "username" not between \'jane\' and \'joe\'', [
            $this->prefix.'username' => '!><jane,joe',
        ]);
    }


    function test_generic_filter_where_in()
    {
        $this->assertQuery('select * where "username" in (\'jane\', \'joe\')', [
            $this->prefix.'username' => 'i=jane,joe',
        ]);
    }


    function test_generic_filter_where_not_in()
    {
        $this->assertQuery('select * where "username" not in (\'jane\', \'joe\')', [
            $this->prefix.'username' => 'i=!jane,joe',
        ]);
    }


    function test_generic_filter_boolean_equals_true()
    {
        $this->assertQuery('select * where "active" = \'1\'', [
            $this->prefix.'active' => 'b=1',
        ]);

        $this->assertQuery('select * where "active" = \'1\'', [
            $this->prefix.'active' => 'b=true',
        ]);

        $this->assertQuery('select * where "active" = \'1\'', [
            $this->prefix.'active' => 'b=yes',
        ]);
    }


    function test_generic_filter_boolean_equals_false()
    {
        $this->assertQuery('select * where "active" = \'0\'', [
            $this->prefix.'active' => 'b=0',
        ]);

        $this->assertQuery('select * where "active" = \'0\'', [
            $this->prefix.'active' => 'b=false',
        ]);

        $this->assertQuery('select * where "active" = \'0\'', [
            $this->prefix.'active' => 'b=no',
        ]);
    }


    function test_generic_filter_boolean_equals_not_true()
    {
        $this->assertQuery('select * where "active" != \'1\'', [
            $this->prefix.'active' => 'b!=1',
        ]);

        $this->assertQuery('select * where "active" != \'1\'', [
            $this->prefix.'active' => 'b!=true',
        ]);

        $this->assertQuery('select * where "active" != \'1\'', [
            $this->prefix.'active' => 'b!=yes',
        ]);
    }


    function test_generic_filter_boolean_equals_not_false()
    {
        $this->assertQuery('select * where "active" != \'0\'', [
            $this->prefix.'active' => 'b!=0',
        ]);

        $this->assertQuery('select * where "active" != \'0\'', [
            $this->prefix.'active' => 'b!=false',
        ]);

        $this->assertQuery('select * where "active" != \'0\'', [
            $this->prefix.'active' => 'b!=no',
        ]);
    }


    function test_generic_filter_timestamp_equals()
    {
        $now = Carbon::now();

        $this->assertQuery('select * where "created_at" = \''.$now->toDateTimeString().'\'', [
            $this->prefix.'created_at' => 't='.$now->timestamp,
        ]);
    }


    function test_generic_filter_timestamp_not_equals()
    {
        $now = Carbon::now();

        $this->assertQuery('select * where "created_at" != \''.$now->toDateTimeString().'\'', [
            $this->prefix.'created_at' => 't!='.$now->timestamp,
        ]);
    }


    function test_generic_filter_timestamp_less_than()
    {
        $now = Carbon::now();
        $this->assertQuery('select * where "created_at" > \''.$now->toDateTimeString().'\'', [
            $this->prefix.'created_at' => 't>'.$now->timestamp,
        ]);
    }


    function test_generic_filter_timestamp_greater_than()
    {
        $now = Carbon::now();
        $this->assertQuery('select * where "created_at" < \''.$now->toDateTimeString().'\'', [
            $this->prefix.'created_at' => 't<'.$now->timestamp,
        ]);
    }


    function test_generic_filter_timestamp_equals_or_less_than()
    {
        $now = Carbon::now();
        $this->assertQuery('select * where "created_at" >= \''.$now->toDateTimeString().'\'', [
            $this->prefix.'created_at' => 't>='.$now->timestamp,
        ]);
    }


    function test_generic_filter_timestamp_equals_or_greater_than()
    {
        $now = Carbon::now();
        $this->assertQuery('select * where "created_at" <= \''.$now->toDateTimeString().'\'', [
            $this->prefix.'created_at' => 't<='.$now->timestamp,
        ]);
    }


    function test_generic_filter_timestamp_between()
    {
        $now  = Carbon::now();
        $then = Carbon::now()->addDay();

        $this->assertQuery('select * where "created_at" between \''.$now->toDateTimeString().'\' and \''.$then->toDateTimeString().'\'',
            [
                $this->prefix.'created_at' => 't><'.$now->timestamp.','.$then->timestamp,
            ]);

        $this->assertQuery('select * where "created_at" between \''.$now->toDateTimeString().'\' and \''.$then->toDateTimeString().'\'',
            [
                $this->prefix.'created_at' => 't><'.$then->timestamp.','.$now->timestamp,
            ]);
    }


    function test_generic_filter_timestamp_not_between()
    {
        $now  = Carbon::now();
        $then = Carbon::now()->addDay();

        $this->assertQuery('select * where "created_at" not between \''.$now->toDateTimeString().'\' and \''.$then->toDateTimeString().'\'',
            [
                $this->prefix.'created_at' => 't!><'.$now->timestamp.','.$then->timestamp,
            ]);

        $this->assertQuery('select * where "created_at" not between \''.$now->toDateTimeString().'\' and \''.$then->toDateTimeString().'\'',
            [
                $this->prefix.'created_at' => 't!><'.$then->timestamp.','.$now->timestamp,
            ]);
    }


    private function assertQuery($expectedQuery, $params)
    {
        $filter = $this->buildFilter(UserFilter::class, http_build_query($params));
        $this->assertEquals($expectedQuery, $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
    }
}
