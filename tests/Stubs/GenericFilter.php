<?php

namespace Kyslik\LaravelFilterable\Test\Stubs;

class GenericFilter extends \Kyslik\LaravelFilterable\Generic\Filter
{


    protected $filterables = ['id', 'name', 'created_at'];


    /**
     * For testing purposes only.
     * @param array $defaults
     *
     * @return array
     * @throws \Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException
     */
    public function appendableDefaults(array $defaults): array
    {
        return parent::appendableDefaults($defaults);
    }


    function filterMap(): array
    {
        return [
            'name' => ['name'],
        ];
    }


    public function name($name)
    {
        return $this->builder->where('name', $name);
    }
}