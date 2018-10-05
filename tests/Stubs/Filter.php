<?php

namespace Kyslik\LaravelFilterable\Test\Stubs;

class Filter extends \Kyslik\LaravelFilterable\Filter
{

    function recent()
    {
        return $this->builder->where('recent', 1);
    }

    function random() {
        return $this->builder;
    }

    function fake() {
        return $this->builder;
    }

    function disabled() {
        return $this->builder;
    }


    // For testing purposes only.
    public function appendableDefaults(array $defaults): array
    {
        return parent::appendableDefaults($defaults);
    }


    /**
     * @return array ex: ['method-name', 'another-method' => 'alias', 'yet-another-method' => ['alias-one', 'alias-two]]
     */
    function filterMap(): array
    {
        return ['active', 'recent' => ['new', 'scheduled'], 'random', 'fake', 'disabled'];
    }
}

