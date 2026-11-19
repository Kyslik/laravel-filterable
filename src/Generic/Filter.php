<?php

namespace Kyslik\LaravelFilterable\Generic;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Kyslik\LaravelFilterable\Exceptions\InvalidSettingsException;
use Kyslik\LaravelFilterable\Filter as BaseFilter;

abstract class Filter extends BaseFilter
{

    protected $filterables = [];

    protected $prefixedFilterables = [];

    /**
     * Maps unprefixed URI filterable key to target Eloquent column or relation.column.
     *
     * @var array
     */
    protected $filterableMap = [];

    /**
     * @var array|null
     */
    protected $chainedFor = null;

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
     * @var \Kyslik\LaravelFilterable\Generic\Templater
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
     * @param \Illuminate\Http\Request                    $request
     * @param \Kyslik\LaravelFilterable\Generic\Templater $templater
     */
    function __construct(Request $request, Templater $templater)
    {
        parent::__construct($request);

        $this->templater = $templater;

        $this->defaultFilterType = config('filterable.default_type', '=');
        $this->prefix            = config('filterable.prefix', 'filter-');

        $this->buildFilterableMap();
        $this->settings();
        $this->determineGroupingOperator();
        $this->loadFilterTypes(config('filterable.filter_types', []));
        $this->prefixFilterables();
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


    /**
     * @inheritdoc
     */
    public function availableFilters(): array
    {
        return array_merge($this->prefixedFilterables, parent::availableFilters());
    }


    /**
     * @inheritdoc
     */
    public function filterMap(): array
    {
        return [];
    }


    /**
     * Define allowed generics, and for which fields.
     * Read more in the documentation https://github.com/Kyslik/laravel-filterable#additional-configuration
     *
     * @return void
     */
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
     * @throws \Kyslik\LaravelFilterable\Exceptions\InvalidSettingsException
     */
    protected function applyGenericFilters()
    {
        $this->builderPresent();

        $groupedRelations = [];

        foreach ($this->determineGenericFilters() as $column => $filter) {
            if (strpos($column, '.') === false) {
                $this->builder = $this->applyCondition($this->builder, $column, $filter, $this->groupingOperator);
                continue;
            }

            $relationPath = Str::beforeLast($column, '.');
            $relationColumn = Str::afterLast($column, '.');

            $this->assertSupportedRelationChain($this->builder->getModel(), $relationPath);

            if ($this->groupingOperator === 'and') {
                $groupedRelations[$relationPath][] = [$relationColumn, $filter];
                continue;
            }

            $this->builder = $this->builder->has(
                $relationPath,
                '>=',
                1,
                $this->groupingOperator,
                function (Builder $query) use ($relationColumn, $filter) {
                    $this->applyCondition($query, $query->qualifyColumn($relationColumn), $filter, 'and');
                }
            );
        }

        foreach ($groupedRelations as $relationPath => $conditions) {
            $this->builder = $this->builder->has(
                $relationPath,
                '>=',
                1,
                'and',
                function (Builder $query) use ($conditions) {
                    foreach ($conditions as [$relationColumn, $filter]) {
                        $this->applyCondition($query, $query->qualifyColumn($relationColumn), $filter, 'and');
                    }
                }
            );
        }

        return $this;
    }


    protected function applyCondition(Builder $query, string $column, array $filter, string $boolean): Builder
    {
        return ($filter['case'] == 'where') ?
            $query->where($column, $filter['operator'], $filter['value'], $boolean) :
            $query->{$filter['case']}($column, $filter['value'], $boolean);
    }


    protected function assertSupportedRelationChain(Model $model, string $relationPath): void
    {
        $currentModel = $model;

        foreach (explode('.', $relationPath) as $segment) {
            if ($segment === '' || ! method_exists($currentModel, $segment)) {
                throw new InvalidSettingsException('Relation \''.$relationPath.'\' is not defined on '.get_class($model).'.');
            }

            $relation = $currentModel->{$segment}();

            if ( ! ($relation instanceof BelongsTo || $relation instanceof HasOne || $relation instanceof MorphOne)) {
                throw new InvalidSettingsException(
                    'Relation \''.$relationPath.'\' must be a 1:1 (HasOne, MorphOne) or BelongsTo relation.'
                );
            }

            $currentModel = $relation->getRelated();
        }
    }


    protected function buildFilterableMap(): void
    {
        $this->filterableMap = [];

        foreach ($this->filterables as $alias => $target) {
            $uriKey = is_string($alias) ? $alias : $target;
            $this->filterableMap[$uriKey] = $target;
        }
    }


    protected function prefixFilterables()
    {
        if (empty($this->filterableMap)) {
            $this->buildFilterableMap();
        }

        $this->prefixedFilterables = array_values(array_map(function ($uriKey) {
            return $this->prefix.$uriKey;
        }, array_keys($this->filterableMap)));
    }


    private function loadFilterTypes($configuration)
    {
        $types = [];
        foreach ($configuration as $type => $value) {
            $types += $value;
        }

        $map = array_map('strlen', array_keys($types));
        array_multisort($map, SORT_DESC, $types);

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
                $targetColumn = $this->resolveFilterableTarget($filterable);
                if ($targetColumn === null) {
                    throw new InvalidSettingsException('Filterable \''.$filterable.'\' is not present in $filterables array.');
                }
                // If $type exists for particular $filterable we append filters, otherwise we replace.
                $this->for[$targetColumn] = (isset($this->for[$targetColumn][$type])) ?
                    [$type => array_unique(array_merge($this->for[$targetColumn][$type], $filters))] : [$type => $filters];
            }
            $this->chainedFor = null;
        } else {
            $otherType = ($type == 'only') ? 'except' : 'only';

            if ( ! empty($this->$otherType)) {
                throw new InvalidSettingsException('You can set global settings for this generic filter only once.');
            }

            $this->$type = $filters;
        }

        return true;
    }


    private function resolveFilterableTarget(string $filterable): ?string
    {
        if (isset($this->filterableMap[$filterable])) {
            return $this->filterableMap[$filterable];
        }

        if (in_array($filterable, $this->filterableMap, true)) {
            return $filterable;
        }

        return null;
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
        $rawQuery = $this->rawQueryParameters();
        $queryBag = $this->request->query->all();
        $sanitizedData = [];

        foreach ($this->filterableMap as $uriKey => $targetColumn) {
            $prefixed = $this->prefix.$uriKey;

            if (strpos($uriKey, '.') === false) {
                $values = $this->request->only($prefixed);
                $value = array_key_exists($prefixed, $values) ? $values[$prefixed] : null;
            } elseif (array_key_exists($prefixed, $rawQuery)) {
                $value = $rawQuery[$prefixed];
            } elseif (array_key_exists($prefixed, $queryBag)) {
                $value = $queryBag[$prefixed];
            } else {
                $mangled = str_replace('.', '_', $prefixed);
                $mangledUriKey = str_replace('.', '_', $uriKey);
                $value = ( ! isset($this->filterableMap[$mangledUriKey]) && array_key_exists($mangled, $queryBag))
                    ? $queryBag[$mangled]
                    : null;
            }

            if ((is_string($value) || is_numeric($value)) && strlen((string) $value) > 0) {
                $sanitizedData[$targetColumn] = $value;
            }
        }

        return $sanitizedData;
    }


    private function rawQueryParameters(): array
    {
        $queryString = (string) $this->request->server->get('QUERY_STRING', '');
        if ($queryString === '') {
            return [];
        }

        $parsed = [];
        foreach (explode('&', $queryString) as $pair) {
            if ($pair === '') {
                continue;
            }
            $parts = explode('=', $pair, 2);
            $parsed[rawurldecode($parts[0])] = isset($parts[1]) ? rawurldecode($parts[1]) : '';
        }

        return $parsed;
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
            if (Str::startsWith($value, $prefix)) {
                $filters[$column] = $prepareFilter($type, remove_prefix($prefix, $value, false));
                break;
            }
        }

        $filters[$column] = $filters[$column] ?? $prepareFilter($this->filterTypes[$this->defaultFilterType], $value);
    }


    private function determineGroupingOperator()
    {
        $operator = $this->request->get(config('filterable.uri_grouping_operator', 'grouping-operator'));

        $this->groupingOperator = is_string($operator) ? strtolower($operator) : null;

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
            $filterTypes = call_user_func([Arr::class, $method], $this->filterTypes, $this->for[$column][$method]);
        } else {
            // Apply global settings
            if ( ! empty($this->only)) {
                // Only logic
                $filterTypes = Arr::only($filterTypes, $this->only);
            } elseif ( ! empty($this->except)) {
                // Except logic
                $filterTypes = Arr::except($filterTypes, $this->except);
            }
        }

        return $filterTypes;
    }
}
