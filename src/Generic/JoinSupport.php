<?php

namespace Kyslik\LaravelFilterable\Generic;

trait JoinSupport
{

    /**
     * @var array
     */
    protected $joins = [];


    /**
     * @param array $arguments
     *
     * @see \Illuminate\Database\Query\Builder::join()
     * @return \Kyslik\LaravelFilterable\Filter|\Kyslik\LaravelFilterable\Generic\JoinSupport
     */
    public function addJoin(...$arguments)
    {
        $signature = $this->joinSignature($arguments);

        if (in_array($signature, $this->joins)) {
            return $this;
        }

        array_push($this->joins, $signature);

        call_user_func_array([$this->builder, 'join'], $arguments);

        return $this;
    }


    /**
     * @param array $arguments
     *
     * @see \Illuminate\Database\Query\Builder::leftJoin()
     * @return \Kyslik\LaravelFilterable\Filter|\Kyslik\LaravelFilterable\Generic\JoinSupport
     */
    public function addLeftJoin(...$arguments)
    {
        $this->fillJoinArguments($arguments, 'left');

        call_user_func_array([$this, 'addJoin'], $arguments);

        return $this;
    }


    /**
     * @param array $arguments
     *
     * @see \Illuminate\Database\Query\Builder::rightJoin()
     * @return \Kyslik\LaravelFilterable\Filter|\Kyslik\LaravelFilterable\Generic\JoinSupport
     */
    public function addRightJoin(...$arguments)
    {
        $this->fillJoinArguments($arguments, 'right');

        call_user_func_array([$this, 'addJoin'], $arguments);

        return $this;
    }


    /**
     * @param array $arguments
     *
     * @see \Kyslik\LaravelFilterable\Filter::addJoin()
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function setJoin(...$arguments)
    {
        call_user_func_array([$this, 'addJoin'], $arguments);

        return $this->builder;
    }


    /**
     * @param array $arguments
     *
     * @see \Kyslik\LaravelFilterable\Filter::addLeftJoin()
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function setLeftJoin(...$arguments)
    {
        call_user_func_array([$this, 'addLeftJoin'], $arguments);

        return $this->builder;
    }


    /**
     * @param array $arguments
     *
     * @see \Kyslik\LaravelFilterable\Filter::addRightJoin()
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function setRightJoin(...$arguments)
    {
        call_user_func_array([$this, 'addRightJoin'], $arguments);

        return $this->builder;
    }


    protected function joinSignature($arguments)
    {
        if ($arguments[1] instanceof \Closure) {
            unset($arguments[1]);
        }

        return ($arguments[4] ?? 'inner').serialize($arguments);
    }


    protected function fillJoinArguments(&$arguments, $type = null)
    {
        $arguments[2] = $arguments[2] ?? null;
        $arguments[3] = $arguments[3] ?? null;
        $arguments[4] = $type;
    }
}