# chillerlan/php-oauth-core

A framework.agnostic PHP OAuth1/2 client that acts as a [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client, fully [PSR-7](https://www.php-fig.org/psr/psr-7/)/[PSR-17](https://www.php-fig.org/psr/psr-17/) compatible.

[![PHP Version Support][php-badge]][php]
[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![Continuous Integration][gh-action-badge]][gh-action]
[![CodeCov][coverage-badge]][coverage]
[![Codacy][codacy-badge]][codacy]
[![Packagist downloads][downloads-badge]][downloads]

[php-badge]: https://img.shields.io/packagist/php-v/chillerlan/php-oauth-core?logo=php&color=8892BF
[php]: https://www.php.net/supported-versions.php
[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-oauth-core.svg?logo=packagist
[packagist]: https://packagist.org/packages/chillerlan/php-oauth-core
[license-badge]: https://img.shields.io/github/license/chillerlan/php-oauth-core.svg
[license]: https://github.com/chillerlan/php-oauth-core/blob/main/LICENSE
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-oauth-core.svg?logo=codecov
[coverage]: https://codecov.io/github/chillerlan/php-oauth-core
[codacy-badge]: https://img.shields.io/codacy/grade/de971588f9a44f1a99e7bbd2a0737951?logo=codacy
[codacy]: https://app.codacy.com/gh/chillerlan/php-oauth-core/dashboard
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-oauth-core.svg?logo=packagist
[downloads]: https://packagist.org/packages/chillerlan/php-oauth-core/stats
[gh-action-badge]: https://img.shields.io/github/actions/workflow/status/chillerlan/php-oauth-core/ci.yml?branch=main&logo=github
[gh-action]: https://github.com/chillerlan/php-oauth-core/actions/workflows/ci.yml?query=branch%3Amain

# Documentation

https://php-oauth.readthedocs.io/

An API documentation created with [phpDocumentor](https://www.phpdoc.org/) can be found at https://chillerlan.github.io/php-oauth-core/ (WIP).
See [the wiki](https://github.com/chillerlan/php-oauth-core/wiki) for advanced documentation and  [`chillerlan/php-oauth-providers`](https://github.com/chillerlan/php-oauth-providers) for already implemented providers.


## Requirements

- PHP 8.1+
  - extensions: `curl`, `json`, `simplexml`, `sodium`, `zlib`
- a [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible HTTP client library of your choice
- [PSR-17](https://www.php-fig.org/psr/psr-17/) compatible Request-, Response- and UriFactories


## Installation

**requires [composer](https://getcomposer.org)**

`composer.json` (note: replace `dev-main` with a [version boundary](https://getcomposer.org/doc/articles/versions.md), e.g. `^5.0`)
```json
{
	"require": {
		"php": "^8.1",
		"chillerlan/php-oauth-core": "dev-main"
	}
}
```
In case you want to keep using `dev-main`, specify the hash of a commit to avoid running into unforeseen issues like so: `dev-main#ff85785139b9531a6c29d41cc161e4878d54491d`


Profit!

# Disclaimer
OAuth tokens are secrets and should be treated as such. Store them in a safe place,
[consider encryption](http://php.net/manual/book.sodium.php). <br/>
I won't take responsibility for stolen auth tokens. Use at your own risk.
