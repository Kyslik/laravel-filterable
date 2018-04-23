<?php

namespace Kyslik\LaravelFilterable\Test;

use Kyslik\LaravelFilterable\Exceptions\InvalidSettingsException;
use Kyslik\LaravelFilterable\Generic\Filter;
use Orchestra\Testbench\TestCase as Orchestra;

class SettingsTest extends Orchestra
{

    function test_calling_only_throws_up()
    {
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage('You can set global settings for this generic filter only once.');
        resolve(FilterOnly::class);
    }


    function test_calling_except_throws_up()
    {
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage('You can set global settings for this generic filter only once.');
        resolve(FilterExcept::class);
    }


    function test_calling_except_with_default_type()
    {
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage('Default filter type \'=\' must not be present in the except array.');
        resolve(FilterExceptDefault::class);
    }


    function test_calling_only_without_default_type()
    {
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage('Default filter type \'=\' must be present in the only array.');
        resolve(FilterOnlyDefault::class);
    }


    function test_calling_column_specific_only_throws_up()
    {
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage('Filterable \'username\' is not present in $filterables array.');
        resolve(FilterColumnSpecificOnly::class);
    }


    function test_calling_column_specific_except_throws_up()
    {
        $this->expectException(InvalidSettingsException::class);
        $this->expectExceptionMessage('Filterable \'username\' is not present in $filterables array.');
        resolve(FilterColumnSpecificExcept::class);
    }

}

class FilterOnly extends Filter
{

    protected function settings()
    {
        $this->setDefaultFilterType('=');
        $this->except(['~']);
        $this->only(['=']);
    }
}

class FilterOnlyDefault extends Filter
{

    protected function settings()
    {
        $this->setDefaultFilterType('=');
        $this->only(['~']);
    }
}

class FilterColumnSpecificOnly extends Filter
{

    protected $filterables = ['id'];


    protected function settings()
    {
        $this->for(['username'])->only(['=']);
    }
}

class FilterExcept extends Filter
{

    protected function settings()
    {
        $this->setDefaultFilterType('=');
        $this->only(['=']);
        $this->except(['~']);
    }
}

class FilterExceptDefault extends Filter
{

    protected function settings()
    {
        $this->setDefaultFilterType('=');
        $this->except(['=']);
    }
}

class FilterColumnSpecificExcept extends Filter
{

    protected $filterables = ['id'];


    protected function settings()
    {
        $this->for(['username'])->except(['~']);
    }
}