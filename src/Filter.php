<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance;

abstract class Filter implements FilterContract
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

    /**
     * @var array
     */
    protected $joins = [];
    /**
     * @return Filter
     */


    public function __construct(Request $request)
    {
        $this->request   = $request;
        $this->filterMap = $this->filterMap();
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Exception
     */
    public function apply(Builder $builder): Builder
    {
        return $this->setBuilder($builder)->applyFilters()->getBuilder();
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
     * @return Filter
     */
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }


    /**
     * @throws \Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance
     */
    protected function builderPresent()
    {
        if (empty($this->builder)) {
            throw new MissingBuilderInstance();
        }
    }

    /**
     * @return Builder
     */
    public function setJoin($join,$key,$joinKey,$joinType = null)
    {
        if(in_array($join, $this->joins)){
            return $this->builder;
        }
        array_push($this->joins,$join);
        if($joinType == "left"){
            return $this->builder->leftJoin($join,$key,$joinKey);
        }else if($joinType == "right"){
            return $this->builder->rightJoin($join,$key,$joinKey);
        }
        return $this->builder->join($join,$key,$joinKey);
    }

    /**
     * @return Builder
     */
    public function setLeftJoin($join,$key,$joinKey)
    {
        return $this->setJoin($join,$key,$joinKey,"left");
    }

    /**
     * @return Builder
     */
    public function setRightJoin($join,$key,$joinKey)
    {
        return $this->setJoin($join,$key,$joinKey,"right");
    }

    /**
     * @return $this
     * @throws \Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance
     * @throws \Exception
     */
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


    private function filters(): array
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

        return $filters ?? [];
    }
}
