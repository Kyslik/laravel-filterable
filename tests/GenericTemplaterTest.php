<?php

namespace Kyslik\LaravelFilterable\Test;

use Carbon\Carbon;
use Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException;
use Kyslik\LaravelFilterable\Generic\Templater;
use Orchestra\Testbench\TestCase as Orchestra;

class GenericTemplaterTest extends Orchestra
{

    /** @var  $templater Templater */
    protected $templater;


    function test_template_timestamp()
    {
        $now    = Carbon::now();
        $actual = $this->templater->apply('timestamp', $now->timestamp);
        $this->assertEquals($now->toDateTimeString(), $actual);
    }


    function test_template_timestamp_throws_up()
    {
        $invalidTimestamp = 'abc';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided timestamp \''.$invalidTimestamp.'\' appears to be invalid.');

        $this->templater->apply('timestamp', $invalidTimestamp);
    }


    function test_template_timestamp_range()
    {
        $now      = Carbon::now();
        $tomorrow = Carbon::tomorrow();

        $actual = $this->templater->apply('timestamp-range', $now->timestamp.','.$tomorrow->timestamp);

        $this->assertEquals([$now->toDateTimeString(), $tomorrow->toDateTimeString()], $actual);
    }


    function test_template_timestamp_range_throws_up_because_of_not_enough_timestamps()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provide exactly two timestamps.');
        $this->templater->apply('timestamp-range', '1');
    }


    function test_template_timestamp_range_throws_up_because_of_too_many_timestamps()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provide exactly two timestamps.');
        $this->templater->apply('timestamp-range', '1,2,3');
    }


    function test_template_range()
    {
        // 0,1 => 0,1
        $this->assertEquals(['0', '1'], $this->templater->apply('range', '0,1'));
        // 1,0 -> 0,1
        $this->assertEquals(['0', '1'], $this->templater->apply('range', '1,0'));
        // -1,-2 => -2,-1
        $this->assertEquals(['-2', '-1'], $this->templater->apply('range', '-1,-2'));
        // 2,-1 => -1,2
        $this->assertEquals(['-1', '2'], $this->templater->apply('range', '2,-1'));
        // 3,-1,2,1,0 => -1,3
        $this->assertEquals(['-1', '3'], $this->templater->apply('range', '3,-1,2,1,0'));
        // 1 => 0,1
        $this->assertEquals(['0', '1'], $this->templater->apply('range', '1'));
        // -1 => -1,0
        //$this->assertEquals(['-1','0'], $this->templater->apply('range', '-1'));
    }


    function test_template_range_throws_up_when_empty_value()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->templater->apply('range', '');
    }


    function test_template_range_throws_up_when_only_zero_value()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->templater->apply('range', '0');
    }


    function test_template_where_in()
    {
        $this->assertEquals(['1', '2', '3'], $this->templater->apply('where-in', '1,2,3'));
        $this->assertEquals(['a', 'b', 'c'], $this->templater->apply('where-in', 'a,b,c'));
    }


    function test_template_boolean()
    {
        $this->assertEquals('1', $this->templater->apply('boolean', '1'));
        $this->assertEquals('1', $this->templater->apply('boolean', 'true'));
        $this->assertEquals('1', $this->templater->apply('boolean', 'yes'));

        $this->assertEquals('0', $this->templater->apply('boolean', '0'));
        $this->assertEquals('0', $this->templater->apply('boolean', 'false'));
        $this->assertEquals('0', $this->templater->apply('boolean', 'no'));
    }


    function test_template_replacer()
    {
        $this->assertEquals('%joe%', $this->templater->apply('%?%', 'joe'));
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->templater = resolve(Templater::class);
    }
}
