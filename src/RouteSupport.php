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
            if ($this->queryHasFilter($request, $filter)) {
                $this->removeFilterFromQuery($request, $filter);
                unset($filters[$filter]);
            }
        }

        return $request->fullUrlWithNiceQuery(array_merge($this->normalizedQueryParameters($request), $filters));
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

        foreach (array_keys($filters) as $filter) {
            $this->removeFilterFromQuery($request, $filter);
        }

        return $request->fullUrlWithNiceQuery($this->normalizedQueryParameters($request));
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

        foreach (array_keys($filters) as $filter) {
            $this->removeFilterFromQuery($request, $filter);
        }

        return $request->fullUrlWithNiceQuery($this->normalizedQueryParameters($request));
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

        foreach (array_keys($filters) as $filter) {
            if ($this->queryHasFilter($request, $filter) && ! $overwrite) {
                unset($filters[$filter]);
            }
        }

        return $request->fullUrlWithNiceQuery(array_merge($this->normalizedQueryParameters($request), $filters));
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

        foreach (array_keys($filters) as $filter) {
            if ($this->queryHasFilter($this->request, $filter)) {
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

        foreach (array_keys($filters) as $filter) {
            if ( ! $this->queryHasFilter($this->request, $filter)) {
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


    private function queryHasFilter(Request $request, string $filter): bool
    {
        if ($request->query->has($filter)) {
            return true;
        }

        if (strpos($filter, '.') !== false) {
            $raw = $this->rawQueryKeys($request);
            if (array_key_exists($filter, $raw)) {
                return true;
            }

            return $request->query->has(str_replace('.', '_', $filter));
        }

        return false;
    }


    private function removeFilterFromQuery(Request $request, string $filter): void
    {
        $request->query->remove($filter);

        if (strpos($filter, '.') !== false) {
            $request->query->remove(str_replace('.', '_', $filter));
        }
    }


    private function normalizedQueryParameters(Request $request): array
    {
        $params = $request->query->all();
        $raw = $this->rawQueryKeys($request);

        foreach ($this->filter->availableFilters() as $available) {
            if (strpos($available, '.') === false) {
                continue;
            }

            $mangled = str_replace('.', '_', $available);
            if (array_key_exists($mangled, $params) && (array_key_exists($available, $raw) || ! in_array($mangled, $this->filter->availableFilters(), true))) {
                $params[$available] = $params[$mangled];
                unset($params[$mangled]);
            }
        }

        return $params;
    }


    private function rawQueryKeys(Request $request): array
    {
        $queryString = (string) $request->server->get('QUERY_STRING', '');
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
