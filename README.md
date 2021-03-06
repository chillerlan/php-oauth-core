# chillerlan/php-oauth-core
A PHP7.4+ OAuth1/2 client with an integrated API wrapper, [loosely based](https://github.com/codemasher/PHPoAuthLib) on [Lusitanian/PHPoAuthLib](https://github.com/Lusitanian/PHPoAuthLib).

[![PHP Version Support][php-badge]][php]
[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![Travis CI][travis-badge]][travis]
[![CodeCov][coverage-badge]][coverage]
[![Scrunitizer CI][scrutinizer-badge]][scrutinizer]
[![Packagist downloads][downloads-badge]][downloads]<br/>
[![Continuous Integration][gh-action-badge]][gh-action]
[![phpDocs][gh-docs-badge]][gh-docs]

[php-badge]: https://img.shields.io/packagist/php-v/chillerlan/php-oauth-core?logo=php&color=8892BF
[php]: https://www.php.net/supported-versions.php
[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-oauth-core.svg
[packagist]: https://packagist.org/packages/chillerlan/php-oauth-core
[license-badge]: https://img.shields.io/github/license/chillerlan/php-oauth-core.svg
[license]: https://github.com/chillerlan/php-oauth-core/blob/main/LICENSE
[travis-badge]: https://img.shields.io/travis/com/chillerlan/php-oauth-core/main.svg?logo=travis
[travis]: https://travis-ci.com/github/chillerlan/php-oauth-core
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-oauth-core.svg?logo=codecov
[coverage]: https://codecov.io/github/chillerlan/php-oauth-core
[scrutinizer-badge]: https://img.shields.io/scrutinizer/g/chillerlan/php-oauth-core.svg?logo=scrutinizer
[scrutinizer]: https://scrutinizer-ci.com/g/chillerlan/php-oauth-core
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-oauth-core.svg
[downloads]: https://packagist.org/packages/chillerlan/php-oauth-core/stats
[gh-action-badge]: https://github.com/chillerlan/php-oauth-core/workflows/Continuous%20Integration/badge.svg
[gh-action]: https://github.com/chillerlan/php-oauth-core/actions
[gh-docs-badge]: https://github.com/chillerlan/php-oauth-core/workflows/Docs/badge.svg
[gh-docs]: https://github.com/chillerlan/php-oauth-core/actions?query=workflow%3ADocs

# Documentation
See [the wiki](https://github.com/chillerlan/php-oauth-core/wiki) for advanced documentation.
An API documentation created with [phpDocumentor](https://www.phpdoc.org/) can be found at https://chillerlan.github.io/php-oauth-core/ (WIP).

## Requirements
- PHP 7.4+
- a [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible HTTP client library of your choice ([there is one included](https://github.com/chillerlan/php-httpinterface), though)
  - optional [PSR-17](https://www.php-fig.org/psr/psr-17/) compatible Request-, Response- and UriFactories
- see [`chillerlan/php-oauth-providers`](https://github.com/chillerlan/php-oauth-providers) for already implemented providers

## Installation
**requires [composer](https://getcomposer.org)**

`composer.json` (note: replace `dev-main` with a [version boundary](https://getcomposer.org/doc/articles/versions.md))
```json
{
	"require": {
		"php": "^7.4 || ^8.0",
		"chillerlan/php-oauth-core": "dev-main"
	}
}
```
Profit!

# Disclaimer
OAuth tokens are secrets and should be treated as such. Store them in a safe place,
[consider encryption](http://php.net/manual/book.sodium.php).<br/>
I won't take responsibility for stolen auth tokens. Use at your own risk.
