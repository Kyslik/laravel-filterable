<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


- [Laravel Filterable](#laravel-filterable)
  - [Installation](#installation)
  - [Before we start](#before-we-start)
    - [Custom filters](#custom-filters)
    - [Generic filters](#generic-filters)
      - [Default operator matrix for generic filters](#default-operator-matrix-for-generic-filters)
  - [Usage](#usage)
    - [Example with custom filters](#example-with-custom-filters)
    - [Example with generic filters](#example-with-generic-filters)
      - [Additional configuration](#additional-configuration)
  - [Testing](#testing)
  - [Changelog](#changelog)
  - [Contributing](#contributing)
  - [Security](#security)
  - [Credits](#credits)
  - [License](#license)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

# Laravel Filterable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kyslik/laravel-filterable.svg?style=flat-square)](https://packagist.org/packages/kyslik/laravel-filterable)
[![Build Status](https://img.shields.io/travis/kyslik/laravel-filterable/master.svg?style=flat-square)](https://travis-ci.org/kyslik/laravel-filterable)
[![Total Downloads](https://img.shields.io/packagist/dt/kyslik/laravel-filterable.svg?style=flat-square)](https://packagist.org/packages/kyslik/laravel-filterable)

This package allows you to easily handle database filtering through query strings. Idea is taken from one of [Jeffrey's videos (behind the paywall)](https://laracasts.com/series/eloquent-techniques/episodes/4). One quick example might look like this: `/users?filter-username=~joe` will result in SQL query `select * from users where "username" like '%joe%'`.

## Installation

You can install the package via composer:

```bash
composer require kyslik/laravel-filterable
```

Laravel will discover the package by itself. If you feel old-school disable auto-discovery and add `Kyslik\LaravelFilterable\FilterableServiceProvider::class` to the providers array in your `config/app.php`.

## Before we start

Package lets us create & apply two kinds of filters **custom** and **generic**.

### Custom filters

**Custom** filters are just like in Jeffrey's video. We define our logic on builder instance and package will apply it via [local scope](https://laravel.com/docs/5.5/eloquent#local-scopes).  
Lets say we want to display recently created records. We create method `recent($minutes = null)` inside our filter class, this method returns Builder instance:

```php
public function recent($minutes = null)
{
    $minutes = (is_numeric($minutes)) ? $minutes : 30;

    return $this->builder->where('created_at', '>=', Carbon\Carbon::now()->subMinutes($minutes));
}
```

Full example is shown [later on](https://github.com/Kyslik/laravel-filterable#example-with-generic-filters).

### Generic filters

**Generic** filters are those defined in [config file](./config/filterable.php). Package supports by default filtering `timestamps`, `ranges`, `ins`, `booleans` and `strings`.

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

While using both **custom** or **generic** filters we must:

1. have [local scope](https://laravel.com/docs/5.5/eloquent#local-scopes) on model with signature `scopeFilter(Builder $query, FILTERNAME $filters)`
2. have particular (`FILTERNAME`) filter class that extends one of:
   - `Kyslik\LaravelFilterable\GenericFilterable` class - enables us to use both **custom** & **generic** filters
   - `Kyslik\LaravelFilterable\Filterable` class - enables us to use only custom filters
3. call scope within a controller

### Example with custom filters

Lets say we want to use filterable on user model. We will have to create filter class `App/Filters/UserFilter.php`, specify `filterMap()` and method with our custom logic.

```php
<?php
namespace App\Filters;

use Kyslik\LaravelFilterable\Filterable;

class UserFilters extends Filterable
{
    public function filterMap()
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

>**Note**: `filterMap()` must be defined and it should return associative array where key is method name and value is either alias or array of aliases

Secondly we will have to add a [local scope](https://laravel.com/docs/5.5/eloquent#local-scopes) to our user model:

```php
class User extends Model 
{
    ...
    public function scopeFilter(Builder $query, UserFilters $filters)
    {
        return $filters->apply($query);
    }
}
```

Finally in user controller we will have to call model's scope:

```php
public function index(User $user, UserFilters $filters) 
{
	return $user->filter($filters)->paginate();
}
```

Now we can visit `users?recent` or `users?recently` or `users?recent=25` and results will be filtered by `recent()` method defined in `UserFilters` class.

### Example with generic filters

Lets say we want to use filterable on user model. We will have to create filter class `App/Filters/UserFilter.php` and specify `$filterables`.

```php
<?php
namespace App\Filters;

use Kyslik\LaravelFilterable\GenericFilterable;

class UserFilters extends GenericFilterable
{
    protected $filterables = ['id', 'username', 'email', 'created_at', 'updated_at'];
}
```

Secondly we will have to add a [local scope](https://laravel.com/docs/5.5/eloquent#local-scopes) to our user model:

```php
class User extends Model 
{
    ...
    public function scopeFilter(Builder $query, UserFilters $filters)
    {
        return $filters->apply($query);
    }
}
```

Finally in our user controller we will have to call model's scope:

```php
public function index(User $user, UserFilters $filters) 
{
	return $user->filter($filters)->paginate();
}
```

We are ready to filter user model!

>**Note**: behind the scenes `GenericFilterable` class extends `Filterable` class thus, using **GenericFilterable** also enables us to apply custom filters defined within the filter class

#### Additional configuration

While using generic filters we may define which generics should be allowed. In our filter class we define `settings()` method in which we specify settings.

```php
...
class UserFilters extends GenericFilterable
{
    protected $filterables = ['id', 'username', 'email', 'created_at', 'updated_at'];
    
    protected function settings()
    {
        // global settings for this filter, pick either "except" or "only" logic
        $this->only(['=', '~', '!~']);
        // $this->except(['!=']);

        // settings applied only to some columns, these settings ignore settings above
        $this->for(['username', 'id'])->only(['!=', '>=', '=', '~']);
        $this->for(['id'])->only(['=', '!=', '~']); //settings for "id" will be re-written
    }
}
```

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
