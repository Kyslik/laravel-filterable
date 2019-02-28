<?php

namespace Kyslik\LaravelFilterable;

use Illuminate\Http\Request;

class RouteSupport
{

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Kyslik\LaravelFilterable\FilterContract
     */
    protected $filter;


    /**
     * RouteSupport constructor.
     *
     * @param \Illuminate\Http\Request                 $request
     * @param \Kyslik\LaravelFilterable\FilterContract $filter
     */
    public function __construct(Request $request, FilterContract $filter)
    {
        $this->request = $request;
        $this->filter  = $filter;
    }


    /**
     * Toggles $filters.
     *
     * @param $filters
     *
     * @return string
     */
    public function toggle($filters): string
    {
        $filters = $this->applicableFilters($filters);

        $request = clone $this->request;

        if (empty($filters)) {
            return $request->fullUrl();
        }

        $queryBag = $request->query;
        // Toggle OFF, whatever stays in $filters is toggled ON.
        foreach (array_keys($filters) as $filter) {
            if ($queryBag->has($filter)) {
                $queryBag->remove($filter);
                unset($filters[$filter]);
            }
        }

        return $request->fullUrlWithNiceQuery(array_merge($queryBag->all(), $filters));
    }


    /**
     * Removes all filters.
     *
     * @return string
     */
    public function truncate(): string
    {
        $filters = force_assoc_array($this->filter->availableFilters());

        $request = clone $this->request;

        $queryBag = $request->query;

        foreach (array_keys($filters) as $filter) {
            $queryBag->remove($filter);
        }

        return $request->fullUrlWithNiceQuery($queryBag->all());
    }


    /**
     * Removes $filters.
     *
     * @param $filters
     *
     * @return string
     */
    public function remove($filters): string
    {
        $filters = $this->applicableFilters($filters);

        $request = clone $this->request;

        if (empty($filters)) {
            return $request->fullUrl();
        }

        $queryBag = $request->query;

        foreach (array_keys($filters) as $filter) {
            $queryBag->remove($filter);
        }

        return $request->fullUrlWithNiceQuery($queryBag->all());
    }


    /**
     * Adds filters that are not present.
     *
     * @param      $filters
     * @param bool $overwrite Should we overwrite present filter's values with new ones?
     *
     * @return string
     */
    public function add($filters, bool $overwrite = false): string
    {
        $filters = $this->applicableFilters($filters);
        $request = clone $this->request;

        if (empty($filters)) {
            return $request->fullUrl();
        }

        $queryBag = $request->query;

        foreach (array_keys($filters) as $filter) {
            if ($queryBag->has($filter) && ! $overwrite) {
                unset($filters[$filter]);
            }
        }

        return $request->fullUrlWithNiceQuery(array_merge($queryBag->all(), $filters));
    }


    /**
     * Adds filters that are not present and merges those that are; $filters takes priority.
     *
     * @param $filters
     *
     * @return string full url with query string
     */
    public function merge($filters): string
    {
        return $this->add($filters, true);
    }


    /**
     * Checks whether any of $filters are currently present in query string.
     *
     * @param $filters
     *
     * @return bool
     */
    public function hasAny($filters): bool
    {
        $filters = $this->applicableFilters($filters);

        if (empty($filters)) {
            return false;
        }

        $queryBag = $this->request->query;

        foreach (array_keys($filters) as $filter) {
            if ($queryBag->has($filter)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Checks whether all of $filters are currently present in query string.
     *
     * @param $filters
     *
     * @return bool
     */
    public function hasAll($filters): bool
    {
        $filters = $this->applicableFilters($filters);

        if (empty($filters)) {
            return false;
        }

        $queryBag = $this->request->query;

        foreach (array_keys($filters) as $filter) {
            if ( ! $queryBag->has($filter)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Checks whether $filter is currently present in query string.
     *
     * @param $filter
     *
     * @return bool
     */
    public function has($filter): bool
    {
        return $this->hasAll($filter);
    }


    private function applicableFilters($filters): array
    {
        if ( ! is_array($filters)) {
            if ( ! is_string($filters) || is_numeric($filters)) {
                return [];
            }
            $filters = [$filters];
        }

        return array_intersect_key(force_assoc_array($filters, ''), force_assoc_array($this->filter->availableFilters()));
    }
}
