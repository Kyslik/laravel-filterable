<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kyslik\LaravelFilterable\Exceptions\InvalidSettingsException;

/**
 * @property array chainedFor | Used internally in order to cheaply check if we are chaining.
 */
abstract class GenericFilterable extends Filterable
{

    protected $filterables = [];

    /**
     * @var string
     * */
    protected $defaultFilterType;

    /**
     * @var string
     * */
    protected $groupingOperator;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var \Kyslik\LaravelFilterable\GenericTemplater
     */
    private $templater;

    /**
     * @var array
     */
    private $filterTypes;

    /**
     * @var array
     */
    private $except = [];

    /**
     * @var array
     */
    private $only = [];

    /**
     * @var array
     */
    private $for = [];


    /**
     * We initialize
     *
     * @param \Illuminate\Http\Request                   $request
     * @param \Kyslik\LaravelFilterable\GenericTemplater $templater
     */
    function __construct(Request $request, GenericTemplater $templater)
    {
        parent::__construct($request);

        $this->templater = $templater;

        $this->defaultFilterType = config('filterable.default_type', '=');
        $this->prefix            = config('filterable.prefix', 'filter-');

        $this->settings();
        $this->determineGroupingOperator();
        $this->loadFilterTypes(config('filterable.filter_types', []));
    }


    /**
     * Firstly we set builder instance passed from Eloquent's Model scope,
     * secondly we apply custom filters if applicable,
     * thirdly we apply generic filters and
     * return builder instance back to scope.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance
     */
    public function apply(Builder $builder): Builder
    {
        return $this->setBuilder($builder)->applyFilters()->applyGenericFilters()->getBuilder();
    }


    public function filterMap(): array
    {
        return [];
    }


    /**
     * @param string $defaultFilterType
     */
    public function setDefaultFilterType($defaultFilterType)
    {
        $this->defaultFilterType = $defaultFilterType;
    }


    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }


    protected function settings()
    {
        //
    }


    /**
     * @param $filterables
     *
     * @return $this
     */
    protected function for($filterables)
    {
        $this->chainedFor = is_array($filterables) ? $filterables : [$filterables];

        return $this;
    }


    /**
     * @param array $filters
     *
     * @return bool
     * @throws \Kyslik\LaravelFilterable\Exceptions\InvalidSettingsException
     */
    protected function except(array $filters)
    {
        if (in_array($this->defaultFilterType, $filters)) {
            throw new InvalidSettingsException('Default filter type \''.$this->defaultFilterType.'\' must not be present in the except array.');
        }

        return $this->prepareSettings($filters, 'except');
    }


    /**
     * @param array $filters
     *
     * @return bool
     * @throws \Kyslik\LaravelFilterable\Exceptions\InvalidSettingsException
     */
    protected function only(array $filters)
    {
        if ( ! in_array($this->defaultFilterType, $filters)) {
            throw new InvalidSettingsException('Default filter type \''.$this->defaultFilterType.'\' must be present in the only array.');
        }

        return $this->prepareSettings($filters, 'only');
    }


    /**
     * @return $this
     * @throws \Kyslik\LaravelFilterable\Exceptions\MissingBuilderInstance
     */
    protected function applyGenericFilters()
    {
        $this->builderPresent();

        foreach ($this->determineGenericFilters() as $column => $filter) {
            $this->builder = ($filter['case'] == 'where') ?
                $this->builder->where($column, $filter['operator'], $filter['value'], $this->groupingOperator) :
                $this->builder->{$filter['case']}($column, $filter['value'], $this->groupingOperator);
        }

        return $this;
    }


    private function loadFilterTypes($configuration)
    {
        $types = [];
        foreach ($configuration as $type => $value) {
            $types += $value;
        }

        array_multisort(array_map('strlen', array_keys($types)), SORT_DESC, $types);
        $this->filterTypes = $types;
    }


    /**
     * @param $filters
     * @param $type
     *
     * @return bool
     * @throws \Kyslik\LaravelFilterable\Exceptions\InvalidSettingsException
     */
    private function prepareSettings($filters, $type): bool
    {
        if (empty($filters)) {
            return false;
        }

        // Handle special case when we are chaining obj->for(['username'])->except|only([]);
        if (isset($this->chainedFor)) {
            foreach ($this->chainedFor as $filterable) {
                if ( ! in_array($filterable, $this->filterables)) {
                    throw new InvalidSettingsException('Filterable \''.$filterable.'\' is not present in $filterables array.');
                }
                // If $type exists for particular $filterable we append filters, otherwise we replace.
                $this->for[$filterable] = (isset($this->for[$filterable][$type])) ?
                    [$type => array_unique(array_merge($this->for[$filterable][$type], $filters))] :
                    [$type => $filters];
            }
            unset($this->chainedFor);
        } else {
            $otherType = ($type == 'only') ? 'except' : 'only';

            if ( ! empty($this->$otherType)) {
                throw new InvalidSettingsException('You can set global settings for this generic filter only once.');
            }

            $this->$type = $filters;
        }

        return true;
    }


    private function determineGenericFilters()
    {
        $filters = [];
        foreach ($this->filters() as $column => $value) {
            $this->transformFilters($filters, $column, $value);
        }

        return $filters;
    }


    /**
     * At first we apply prefix to $this->filterables and get all data from request,
     * after that we remove prefix from keys so we can easily manipulate it.
     *
     * @return array
     */
    private function filters()
    {
        // Grab all data from query strings that start with the prefix $this->prefix.
        $data = $this->request->only(array_map(function ($value) {
            return $this->prefix.$value;
        }, $this->filterables));

        // Get rid of empty values.
        $data = array_filter($data, 'strlen');

        // Clean up data and remove the prefix 'filter-' from keys.
        foreach ($data as $key => $value) {
            $sanitizedData[remove_prefix($this->prefix, $key, false)] = $value;
        }

        return $sanitizedData ?? [];
    }


    /**
     * @param $filters
     * @param $column
     * @param $value
     */
    private function transformFilters(&$filters, $column, $value)
    {
        $prepareFilter = function ($type, $value) {
            return [
                'case'     => $type['case'],
                'operator' => $type['operator'],
                'value'    => $this->templater->apply($type['template'], $value),
            ];
        };

        foreach ($this->prepareFilters($column) as $prefix => $type) {
            if (starts_with($value, $prefix)) {
                $filters[$column] = $prepareFilter($type, remove_prefix($prefix, $value, false));
                break;
            }
        }

        $filters[$column] = $filters[$column] ?? $prepareFilter($this->filterTypes[$this->defaultFilterType], $value);
    }


    private function determineGroupingOperator()
    {
        $this->groupingOperator =
            strtolower($this->request->get(config('filterable.uri_grouping_operator', 'grouping-operator'), null));

        if (is_null($this->groupingOperator) or ! in_array($this->groupingOperator, ['and', 'or'], true)) {
            $this->groupingOperator = config('filterable.default_grouping_operator', 'and');
        }
    }


    /**
     * @param $column
     *
     * @return array|mixed
     */
    private function prepareFilters($column)
    {
        $filterTypes = $this->filterTypes;

        if (isset($this->for[$column])) {
            // Special care for this particular $column because of settings.
            $method      = key($this->for[$column]);
            $filterTypes = call_user_func('array_'.$method, $this->filterTypes, $this->for[$column][$method]);
        } else {
            // Apply global settings
            if ( ! empty($this->only)) {
                // Only logic
                $filterTypes = array_only($filterTypes, $this->only);
            } elseif ( ! empty($this->except)) {
                // Except logic
                $filterTypes = array_except($filterTypes, $this->except);
            }
        }

        return $filterTypes;
    }
}