<?php

return [

    'namespace'                 => 'Filters',

    // Default filter types
    // You may freely change operator keys '=', 't='... to suite your needs.
    'filter_types'              => [
        // accepts UNIX timestamp
        'timestamp' => [
            't!><' => ['case' => 'whereNotBetween', 'operator' => null, 'template' => 'timestamp-range'],
            't><'  => ['case' => 'whereBetween', 'operator' => null, 'template' => 'timestamp-range'],
            't>='  => ['case' => 'where', 'operator' => '>=', 'template' => 'timestamp'],
            't<='  => ['case' => 'where', 'operator' => '<=', 'template' => 'timestamp'],
            't>'   => ['case' => 'where', 'operator' => '>', 'template' => 'timestamp'],
            't<'   => ['case' => 'where', 'operator' => '<', 'template' => 'timestamp'],
            't!='  => ['case' => 'where', 'operator' => '!=', 'template' => 'timestamp'],
            't='   => ['case' => 'where', 'operator' => '=', 'template' => 'timestamp'],
        ],
        // accepts 1, 0, true, false, yes, no
        'boolean'   => [
            'b='  => ['case' => 'where', 'operator' => '=', 'template' => 'boolean'],
            'b!=' => ['case' => 'where', 'operator' => '!=', 'template' => 'boolean'],
        ],
        // accepts string or comma separated list
        'string'    => [
            '!><' => ['case' => 'whereNotBetween', 'operator' => null, 'template' => 'range'],
            '><'  => ['case' => 'whereBetween', 'operator' => null, 'template' => 'range'],
            '!~'  => ['case' => 'where', 'operator' => 'not like', 'template' => '%?%'],
            '~'   => ['case' => 'where', 'operator' => 'like', 'template' => '%?%'],
            '>='  => ['case' => 'where', 'operator' => '>=', 'template' => null],
            '<='  => ['case' => 'where', 'operator' => '<=', 'template' => null],
            '>'   => ['case' => 'where', 'operator' => '>', 'template' => null],
            '<'   => ['case' => 'where', 'operator' => '<', 'template' => null],
            '!='  => ['case' => 'where', 'operator' => '!=', 'template' => null],
            '='   => ['case' => 'where', 'operator' => '=', 'template' => null],
        ],
        // accepts comma separated list, by default
        'in'        => [
            'i='  => ['case' => 'whereIn', 'operator' => null, 'template' => 'where-in'],
            'i=!' => ['case' => 'whereNotIn', 'operator' => null, 'template' => 'where-in'],
        ],
    ],

    // In case package does not match any of filters above it'll use '=' filter type.
    // This configuration can be overridden per filter.
    'default_type'              => '=',

    // Example: users?filter-id=>=1
    'prefix'                    => 'filter-',

    // When generating queries with multiple filters default_grouping_operator is used between the filters
    // Example: filter-id=1&filter-name=Joe will result in where id = 1 'default_grouping_operator' where name = Joe
    'default_grouping_operator' => 'and',

    'uri_grouping_operator' => 'grouping-operator',
    
     // the separator used for "in" filters in the query string
    'in_separator' => ',',
];
