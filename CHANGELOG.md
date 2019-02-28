# Changelog

All notable changes to `kyslik/laravel-filterable` will be documented in this file

## 2.0.2 - 2019-02-28

### Added

- support for Laravel 5.8
  
### Improved

- TravisCI configuration

### Removed

- dependency on Laravel helpers (`laravel/helpers`)
  - now using `Illuminate\Support\Arr::` / `Illuminate\Support\Str::` instead

## 2.0.1 - 2018-10-05

### Added

- partial route support (see [#11](https://github.com/Kyslik/laravel-filterable/issues/11)):
  - `toggle`, `add`, `merge`, `remove`, `truncate` 
    - generates urls with *proper* query-strings
  - `hasAny`, `hasAll` (alias `has`)
    - returns `bool`
- macros
  - `Request::fullUrlWithNiceQuery`
  - `Arr::query` which is missing in Laravel 5.6

### Fixes

- composer.json - drop support for Laravel 5.5

### Improved

- test suite

## 2.0.0 - 2018-10-01

### Added

- default filtering see [Additional features](https://github.com/Kyslik/laravel-filterable#additional-features) section

### Changed

- trait `JoinSupport` namespace moved up one level
- signature of [`FilterContract`](https://github.com/Kyslik/laravel-filterable/blob/master/src/FilterContract.php)
- dropped support for Laravel 5.5
  - **reason**: while using default filtering; filter needs to `abort(redirect())`, which was introduced in Laravel 5.6
  
### Improved

- test-suite
- readme

## 1.1.3 - 2018-09-04

### Added

- support for **Laravel 5.7**
- trait JoinSupport.php see [PR#9](https://github.com/Kyslik/laravel-filterable/pull/9) for more info

## 1.1.2 - 2018-05-31

### Fixed

- required option `-g` see https://github.com/Kyslik/laravel-filterable/pull/6

## 1.1.1 - 2018-05-30

### Added

- command `make:filter`

### Improved

- readme

## 1.1.0 - 2018-04-23

### Added

- support for Laravel 5.6

### Changed

- requires PHP version `>=7.1`
- signature of `filterMap` function, removed abstract implementation
- directory structure & name-spacing of `Generic*`
- renamed `Filterable` classes to `Filter`
- renamed `FilterableTrait` trait to `Filterable`

### Improved

- testing suite
- readme

## 1.0.2 - 2017-12-22

### Added

- possibility to use different grouping operators (`AND`, `OR`) see #2

### Improved

- testing suite

## 1.0.1 - 2017-11-25

### Added

- FilterableTrait

## 1.0.0 - 2017-11-17

### Added

- everything, initial release
