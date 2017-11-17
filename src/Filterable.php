<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance;

abstract class Filterable implements FilterableContract
{

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $filterMap;


    public function __construct(Request $request)
    {
        $this->request   = $request;
        $this->filterMap = $this->filterMap();
    }


    public function apply(Builder $builder)
    {
        return $this->setBuilder($builder)->applyFilters()->getBuilder();
    }


    /**
     * We define filterMap method and throw exception right away so user is alerted of the fact,
     * that he needs to implement this method in his own code.
     *
     * @return array ex: ['method-name', 'another-method' => 'alias', 'yet-another-method' => ['alias-one', 'alias-two]]
     * @throws \Exception
     */
    public function filterMap()
    {
        throw new \Exception('Method \'filterMap\' is missing on the object \''.__CLASS__.'\'.');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return Filterable
     */
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }


    protected function builderPresent()
    {
        if (empty($this->builder)) {
            throw new MissingBuilderInstance();
        }
    }


    protected function applyFilters()
    {
        $this->builderPresent();

        foreach ($this->filters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->builder = (is_null($value)) ? $this->$filter() : $this->$filter($value);
                continue;
            }
            throw new \Exception('Filter \''.$filter.'\' is declared in \'filterMap\', but it does not exist.');
        }

        return $this;
    }


    private function filters()
    {
        if (empty($this->filterMap)) {
            return [];
        }

        foreach ($this->filterMap as $filter => $value) {
            $method = (is_string($filter)) ? $filter : $value;

            // head([]) === false, we check if head returns false and remove that item from array
            if (($filters[$method] = head($this->request->only($value))) === false) {
                unset($filters[$method]);
            }
        }

        return $filters;
    }
}