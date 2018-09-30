# Laravel Filterable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/Kyslik/laravel-filterable.svg?style=flat-square)](https://packagist.org/packages/kyslik/laravel-filterable)
[![Build Status](https://img.shields.io/travis/Kyslik/laravel-filterable/master.svg?style=flat-square)](https://travis-ci.org/Kyslik/laravel-filterable)
[![Total Downloads](https://img.shields.io/packagist/dt/kyslik/laravel-filterable.svg?style=flat-square)](https://packagist.org/packages/kyslik/laravel-filterable)

This package allows you to easily handle database filtering through query strings. The idea is taken from one of the [Jeffrey's videos (behind the paywall)](https://laracasts.com/series/eloquent-techniques/episodes/4). One quick example might look like this: `/users?filter-username=~joe` will result in SQL query `select * from users where "username" like '%joe%'`.

## Installation

You can install the package via composer:

```bash
composer require kyslik/laravel-filterable
```

Laravel will discover the package by itself. If you feel old-school, disable auto-discovery and add `Kyslik\LaravelFilterable\FilterableServiceProvider::class` to the providers array in your `config/app.php`.

You may continue by publishing [configuration](./config/filterable.php) by issuing following artisan command `php artisan vendor:publish`.

## Introduction

Package lets you to create && apply two kinds of filters **custom** and **generic**.

### Custom filters

**Custom** filters are just like in Jeffrey's video. You define a logic on a builder instance and package applies it via [local scope](https://laravel.com/docs/5.7/eloquent#local-scopes).

Let's say a product requires displaying recently created records. You create a method `recent($minutes = null)` inside a filter class, which returns Builder instance:

```php
public function recent($minutes = null): \Illuminate\Database\Eloquent\Builder
{
    $minutes = (is_numeric($minutes)) ? $minutes : 30;

    return $this->builder->where('created_at', '>=', Carbon\Carbon::now()->subMinutes($minutes));
}
```

> **Note**: full example is shown [later on](https://github.com/Kyslik/laravel-filterable#example-with-generic-filters)

### Generic filters

**Generic** filters are those defined in [config file](./config/filterable.php). By default, the package supports filtering `timestamps`, `ranges`, `ins`, `booleans` and `strings`.

```
/?filter-created_at=t>=1510952444
/?filter-id=><1,19
/?filter-id=i=1,5,10,12
/?filter-admin=b=yes
/?filter-username=joe
/?filter-username=~joe
/?filter-username=~joe&filter-admin=b=yes&filter-created_at=t=1510952444
```

#### Default operator matrix for generic filters

| **operator** |               **accepts**              | **description**       |
|:------------:|:--------------------------------------:|-----------------------|
|      `=`     |                `string`                | equal                 |
|     `!=`     |                `string`                | not equal             |
|      `>`     |                `string`                | greater than          |
|      `<`     |                `string`                | less than             |
|     `>=`     |                `string`                | equal or greater than |
|     `<=`     |                `string`                | equal or less than    |
|      `~`     |                `string`                | like                  |
|     `!~`     |                `string`                | not like              |
|     `><`     |          comma separated list          | between               |
|     `!><`    |          comma separated list          | not between           |
|     `i=`     |          comma separated list          | in                    |
|     `i!=`    |          comma separated list          | not in                |
|     `b=`     | `1`, `0`, `true`, `false`, `yes`, `no` | equal                 |
|     `b!=`    | `1`, `0`, `true`, `false`, `yes`, `no` | not equal             |
|     `t=`     |             UNIX timestamp             | equal                 |
|     `t!=`    |             UNIX timestamp             | not equal             |
|     `t>`     |             UNIX timestamp             | greater than          |
|     `t<`     |             UNIX timestamp             | less than             |
|     `t>=`    |             UNIX timestamp             | equal or greater than |
|     `t<=`    |             UNIX timestamp             | equal or less than    |
|     `t><`    |             UNIX timestamp             | between               |
|    `t!><`    |             UNIX timestamp             | not between           |

## Usage

While using both **custom** or **generic** filters you must:

1. have [local scope](https://laravel.com/docs/5.7/eloquent#local-scopes) on model with the signature `scopeFilter(Builder $query, FILTERNAME $filters)`
2. have particular (`FILTERNAME`) filter class that extends one of:
   - `Kyslik\LaravelFilterable\Generic\Filter` class - allows usage of both **custom** & **generic** filters
   - `Kyslik\LaravelFilterable\Filter` class - allows usage of only **custom** filters
3. call a scope within a controller

#### make:filter command

You can use the following command to create a new filter. 
```bash
php artisan make:filter SomeFilter
```

This will create a new **Custom** filter in the **app/Filters** directory. To create a **Generic** filter just add the `--generic` (`-g`) flag to the command:
```bash
php artisan make:filter SomeGenericFilter -g
```
Lastly, you can override the default namespace by changing the **namespace** config value e.g.

**config/filterable.php**
```php
return [
    'namespace' => 'Http\Filters',
    ...
];
```

### Example with custom filters

Let's say you want to use filterable on `User` model. You will have to create the filter class `App/Filters/UserFilter.php` (`php artisan make:filter UserFilter`), specify `filterMap()` and **filter** method (`recent(...)`) with the custom logic.

```php
<?php
namespace App\Filters;

use Kyslik\LaravelFilterable\Filter;

class UserFilter extends Filter
{
    public function filterMap(): array
    {
        return ['recent' => ['recently', 'recent']];
    }

    public function recent($minutes = null)
    {
        $minutes = (is_numeric($minutes)) ? $minutes : 30;

        return $this->builder->where('created_at', '>=', \Carbon\Carbon::now()->subMinutes($minutes)->toDateTimeString());
    }
}
```

>**Note**: `filterMap()` shall return an associative array where **key** is a method name and **value** is either alias or array of aliases

Now add a [local scope](https://laravel.com/docs/5.7/eloquent#local-scopes) to the `User` model via [Filterable](https://github.com/Kyslik/laravel-filterable/blob/master/src/Filterable.php):

```php
use Kyslik\LaravelFilterable\Filterable;

...
class User extends Model
{
    use Filterable;
    ...
}
```

Finally, call the scope in a controller like so:

```php
use App\Filters\UserFilter;
...
public function index(User $user, UserFilter $filters)
{
    return $user->filter($filters)->paginate();
}
```

Now end-user can visit `users?recent` or `users?recently` or `users?recent=25` and results will be filtered by `recent()` method defined in `UserFilter` class.

### Example with generic filters

Let's say you want to use generic filters on `User` model. You will have to create filter class `App/Filters/UserFilter.php` (`php artisan make:filter UserFilter -g`) and specify `$filterables` just like below:

```php
<?php
namespace App\Filters;

use Kyslik\LaravelFilterable\Generic\Filter;

class UserFilter extends Filter
{
    protected $filterables = ['id', 'username', 'email', 'created_at', 'updated_at'];
}
```

Next, you will have to add a [local scope](https://laravel.com/docs/5.7/eloquent#local-scopes) to the `User` model via [Filterable](https://github.com/Kyslik/laravel-filterable/blob/master/src/Filterable.php):

```php
use Kyslik\LaravelFilterable\Filterable;

...
class User extends Model
{
    use Filterable;
    ...
}
```

Finally, call the scope in a controller like so:

```php
use App\Filters\UserFilter;
...
public function index(User $user, UserFilter $filters)
{
    return $user->filter($filters)->paginate();
}
```


Now you are ready to filter `User` model.

>**Note**: behind the scenes `...\Generic\Filter` class extends `Filter` class, therefore using **`...\Generic\Filter`** also enables you to apply custom filters defined within the filter class

#### Additional configuration

While using generic filters you may define which generics should be allowed. Define `settings()` method in a filter class, see below:

```php
use Kyslik\LaravelFilterable\Generic\Filter
...
class UserFilter extends Filter
{
    protected $filterables = ['id', 'username', 'email', 'created_at', 'updated_at'];

    protected function settings()
    {
        // global settings for this filter, pick either "except" or "only" logic
        $this->only(['=', '~', '!~']);
        // $this->except(['!=']);

        // settings applied only to some columns, these settings ignore the "global" settings above
        $this->for(['username', 'id'])->only(['!=', '>=', '=', '~']);
        $this->for(['id'])->only(['=', '!=', '~']); // settings for "id" will be re-written
    }
}
```

### Additional features

#### Default filtering

In case you need to apply a filter when no filter is applied yet (determined by what query-string contains at the given request), you can use the following code in the controller:

```php
public function index(User $user, UserFilter $filter)
{
    // will redirect and "apply" the `recent` and `filter-id` filters 
    // if not a single filter from UserFilter is applied
    $filter->default(['recent' => now()->toDateTimeString(), 'filter-id' => '!=5']);

    return $user->filter($filter)->paginate();
}
```

End-user is going be redirected from `http://filters.test/users` to `http://filters.test/users?recent=2018-10-01 13:52:40&filter-id=!=5`. 
In case the filter that you specify as *default* does not exist `Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException` is thrown.

> **Caution**: be careful of **infinite redirects**

You can read more about the feature in the [original issue #10](https://github.com/Kyslik/laravel-filterable/issues/10).

#### JoinSupport for filters

TBA

You can read more about the feature in the [original PR #9](https://github.com/Kyslik/laravel-filterable/pull/9).

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email martin.kiesel@gmail.com instead of using the issue tracker.

## Credits

- [kyslik](https://github.com/kyslik)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
