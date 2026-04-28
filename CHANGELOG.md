# Changelog

All notable changes to `laravel-export` will be documented in this file

## 1.4.0 - 2026-04-28

### What's Changed

* Bump dependabot/fetch-metadata from 2.5.0 to 3.0.0 by @dependabot[bot] in https://github.com/spatie/laravel-export/pull/149
* Bump dependabot/fetch-metadata from 3.0.0 to 3.1.0 by @dependabot[bot] in https://github.com/spatie/laravel-export/pull/151
* Use spatie/crawler:^9.1 and allow streaming by @PatrickePatate in https://github.com/spatie/laravel-export/pull/152

### New Contributors

* @PatrickePatate made their first contribution in https://github.com/spatie/laravel-export/pull/152

**Full Changelog**: https://github.com/spatie/laravel-export/compare/1.3.0...1.4.0

## 1.3.0 - 2026-03-06

### What's Changed

- Laravel 13.x Compatibility

## 1.2.2 - 2025-11-28

### What's Changed

* Update issue template by @AlexVanderbist in https://github.com/spatie/laravel-export/pull/140

### New Contributors

* @AlexVanderbist made their first contribution in https://github.com/spatie/laravel-export/pull/140

**Full Changelog**: https://github.com/spatie/laravel-export/compare/1.2.1...1.2.2

## 1.2.1 - 2025-08-18

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/spatie/laravel-export/pull/134
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/spatie/laravel-export/pull/136
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/spatie/laravel-export/pull/135
* Bump actions/checkout from 3 to 5 by @dependabot[bot] in https://github.com/spatie/laravel-export/pull/139
* Fix cross-platform test compatibility and ignore PHPUnit cache by @AMoktar in https://github.com/spatie/laravel-export/pull/137
* Add URL encoding support for filesystem paths with query parameters by @AMoktar in https://github.com/spatie/laravel-export/pull/138

### New Contributors

* @AMoktar made their first contribution in https://github.com/spatie/laravel-export/pull/137

**Full Changelog**: https://github.com/spatie/laravel-export/compare/1.2.0...1.2.1

## 1.2.0 - 2025-03-08

### What's Changed

* Allow redirects export by @plakhin in https://github.com/spatie/laravel-export/pull/131

### New Contributors

* @plakhin made their first contribution in https://github.com/spatie/laravel-export/pull/131

**Full Changelog**: https://github.com/spatie/laravel-export/compare/1.1.2...1.2.0

## 1.1.2 - 2025-02-20

### What's Changed

* Bump dependabot/fetch-metadata from 1.6.0 to 2.2.0 by @dependabot in https://github.com/spatie/laravel-export/pull/123
* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/spatie/laravel-export/pull/126
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/spatie/laravel-export/pull/128
* Laravel 12.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-export/pull/129

### New Contributors

* @laravel-shift made their first contribution in https://github.com/spatie/laravel-export/pull/129

**Full Changelog**: https://github.com/spatie/laravel-export/compare/1.1.1...1.1.2

## 1.1.1 - 2024-04-16

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.3.1 to 2.4 by @dependabot in https://github.com/spatie/laravel-export/pull/121
* Add X-Laravel-Export header to request on export path by @aguingand in https://github.com/spatie/laravel-export/pull/120

**Full Changelog**: https://github.com/spatie/laravel-export/compare/1.1.0...1.1.1

## 1.1.0 - 2024-03-13

### What's Changed

* Bump actions/cache from 3 to 4 by @dependabot in https://github.com/spatie/laravel-export/pull/113
* Add support for Laravel 11 by @olssonm in https://github.com/spatie/laravel-export/pull/115

### New Contributors

* @olssonm made their first contribution in https://github.com/spatie/laravel-export/pull/115

**Full Changelog**: https://github.com/spatie/laravel-export/compare/1.0.1...1.1.0

## 1.0.1 - 2024-01-11

### What's Changed

* fix wink url + suggest more alternatives by @smknstd in https://github.com/spatie/laravel-export/pull/111
* Bumped composer packages by @boydbloemsma in https://github.com/spatie/laravel-export/pull/112

### New Contributors

* @smknstd made their first contribution in https://github.com/spatie/laravel-export/pull/111
* @boydbloemsma made their first contribution in https://github.com/spatie/laravel-export/pull/112

**Full Changelog**: https://github.com/spatie/laravel-export/compare/1.0.0...1.0.1

## 1.0.0 - 2024-01-08

### What's Changed

* Add X-Laravel-Export header by @aguingand in https://github.com/spatie/laravel-export/pull/110

### New Contributors

* @aguingand made their first contribution in https://github.com/spatie/laravel-export/pull/110

**Full Changelog**: https://github.com/spatie/laravel-export/compare/0.3.11...1.0.0

## 0.3.11 - 2023-02-10

- Add support for PHP 8.2
- Add support for Laravel 10
- Refactor tests to Pest

## 0.3.10 - 2022-02-01

- Add support for PHP 8.1
- Add support for Laravel 9

## 0.3.9 - 2021-04-19

- add support for PHP 8

## 0.3.8 - 2021-04-17

- add $foundOnUrl to output message on exception (#70)

## 0.3.7 - 2020-10-24

- update dependencies

## 0.3.6 - 2020-10-02

- added version "^7.0" for `guzzlehttp/guzzle`

## 0.3.4 - 2020-09-09

- Support Laravel 8

## 0.3.3 - 2020-08-05

- Broken pages now throw an exception when crawling
- Internal links aren't normalized anymore when crawling

## 0.3.2 - 2020-04-20

- Add `--skip-all`, `--skip-before`, and ``--skip-after` command flags
- Fix `symfony/process` version 5 support

## 0.3.1 - 2020-03-21

- Add directory check

## 0.3.0 - 2020-03-20

- Add support for Laravel 7

## 0.1.5 - 2019-09-20

- fix homepage url

## 0.1.4 - 2019-09-11

- Add support for Laravel 6
