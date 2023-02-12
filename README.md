# chillerlan/php-oauth-core
A PHP7.4+ OAuth1/2 client with an integrated API wrapper, [loosely based](https://github.com/codemasher/PHPoAuthLib) on [Lusitanian/PHPoAuthLib](https://github.com/Lusitanian/PHPoAuthLib).

[![PHP Version Support][php-badge]][php]
[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![CodeCov][coverage-badge]][coverage]
[![Scrunitizer CI][scrutinizer-badge]][scrutinizer]
[![Packagist downloads][downloads-badge]][downloads]<br/>
[![Continuous Integration][gh-action-badge]][gh-action]

[php-badge]: https://img.shields.io/packagist/php-v/chillerlan/php-oauth-core?logo=php&color=8892BF
[php]: https://www.php.net/supported-versions.php
[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-oauth-core.svg?logo=packagist
[packagist]: https://packagist.org/packages/chillerlan/php-oauth-core
[license-badge]: https://img.shields.io/github/license/chillerlan/php-oauth-core.svg
[license]: https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/LICENSE
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-oauth-core.svg?logo=codecov
[coverage]: https://codecov.io/github/chillerlan/php-oauth-core
[scrutinizer-badge]: https://img.shields.io/scrutinizer/g/chillerlan/php-oauth-core.svg?logo=scrutinizer
[scrutinizer]: https://scrutinizer-ci.com/g/chillerlan/php-oauth-core
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-oauth-core.svg?logo=packagist
[downloads]: https://packagist.org/packages/chillerlan/php-oauth-core/stats
[gh-action-badge]: https://img.shields.io/github/actions/workflow/status/chillerlan/php-oauth-core/ci.yml?logo=github&branch=v4.x-php7.4
[gh-action]: https://github.com/chillerlan/php-oauth-core/actions/workflows/ci.yml?query=branch%3Aphp-7.4

# Documentation

## Requirements
- PHP 7.4+
- a [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible HTTP client library of your choice ([there is one included](https://github.com/chillerlan/php-httpinterface), though)
  - optional [PSR-17](https://www.php-fig.org/psr/psr-17/) compatible Request-, Response- and UriFactories
- see [`chillerlan/php-oauth-providers`](https://github.com/chillerlan/php-oauth-providers/tree/v4.x-php7.4/) for already implemented providers

## Installation
**requires [composer](https://getcomposer.org)**

`composer.json` (note: replace `dev-v4.x-php7.4` with a [version boundary](https://getcomposer.org/doc/articles/versions.md))
```json
{
	"require": {
		"php": "^7.4 || ^8.0",
		"chillerlan/php-oauth-core": "dev-v4.x-php7.4"
	}
}
```
In case you want to keep using `dev-v4.x-php7.4`, specify the hash of a commit to avoid running into unforseen issues like so: `dev-main#ff85785139b9531a6c29d41cc161e4878d54491d`

Profit!

## Usage

### A minimal OAuth1 provider
```php
use chillerlan\OAuth\Core\OAuth1Provider;

class MyOauth1Provider extends Oauth1Provider{

	protected string $authURL         = 'https://example.com/oauth/authorize';
	protected string $accessTokenURL  = 'https://example.com/oauth/access_token';
	protected string $requestTokenURL = 'https://example.com/oauth/request_token';
	protected ?string $apiURL         = 'https://api.example.com';

}
```

### A minimal OAuth2 provider
```php
use chillerlan\OAuth\Core\OAuth2Provider;

class MyOauth2Provider extends Oauth2Provider implements ClientCredentials, CSRFToken, TokenRefresh{

	protected string $authURL          = 'https://example.com/oauth2/authorize';
	protected string $accessTokenURL   = 'https://example.com/oauth2/token';
	protected ?string $apiURL          = 'https://api.example.com/';
	protected ?string $userRevokeURL   = 'https://account.example.com/apps/';

	// optional
	protected int $authMethod          = self::AUTH_METHOD_HEADER;
	protected string $authMethodHeader = 'OAuth';
	protected string $scopesDelimiter  = ',';
}
```

### Testing
If you just wrote your own provider implementation, you also might want to test it.
This library brings several abstract tests that you can use.
In order to use them, you need to add the PSR-4 namespaces to your project's `composer.json` like so:
```json
{
	...

	"autoload": {
		"psr-4": {
			"my\\project\\source\\": "src/"
		}
	},
	"autoload-dev": {
		"psr-4": {
			"my\\project\\test\\": "tests/",

			...

			"chillerlan\\OAuthTest\\": "vendor/chillerlan/php-oauth-core/tests"
		}
	}
}
```

The [basic unit test](https://github.com/chillerlan/php-oauth-core/tree/v4.x-php7.4/tests/Providers) looks as follows:
```php
namespace my\project\test;

use my\project\source\MyProvider;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \my\project\source\MyProvider $provider
 */
class MyProviderTest extends OAuth2ProviderTestAbstract{

	protected $FQN = MyProvider::class;

}
```
That is all! You'll likely only need to add/extend methods in case you need to cover edge cases or additional features of your provider implementation.

In case you want to run tests against a live API (you will need to obtain an access token and put the `AccessToken` JSON into the config dir), you can use the [abstract API tests](https://github.com/chillerlan/php-oauth-providers/tree/v4.x-php7.4/tests) from within a clone of the [`php-oauth-providers`](https://github.com/chillerlan/php-oauth-providers) library.
The live API tests are disabled on CI and you need to enable them explicit by changing the value of [`TEST_IS_CI`](https://github.com/chillerlan/php-oauth-providers/blob/062dd541fe47551898a0fd654bd4e4ba5eda249d/phpunit.xml.dist#L28) to `false` in your project's phpunit.xml.
```php
namespace my\project\test;

use my\project\source\MyProvider;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \my\project\source\MyProvider $provider
 */
class MyProviderAPITest extends OAuth2APITestAbstract{

	protected $FQN = MyProvider::class;
	protected $CFG = __DIR__.'/../config';
	protected $ENV = 'MYPROVIDER'; // the prefix for the provider's name in .env

	// your live API tests here
	public function testIdentity(){
		$r = $this->provider->identity();
		$this->assertSame($this->testuser, $this->responseJson($r)->id);
	}

}
```

## API

### OAuthInterface
The `OAuthInterface` (abstract [`OAuthProvider`](https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/src/Core/OAuthProvider.php)) implements the [PSR-18 `ClientInterface`](https://www.php-fig.org/psr/psr-18/) (a PSR-18 compatible http client is still required),
the [PSR-3 `LoggerAwareInterface`](https://www.php-fig.org/psr/psr-3/) as well as the
[`ApiClientInterface`](https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/src/MagicAPI/ApiClientInterface.php)
and offers basic methods that are common to the OAuth 1/2 interfaces, all supplied in the abstract class [`OAuthProvider`](https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/src/Core/OAuthProvider.php).
Further, custom [PSR-17 HTTP factories](https://www.php-fig.org/psr/psr-17/) can be supplied.

method | return | info
------ | ------ | ----
`getAuthURL(array $params = null)` | PSR-7 `UriInterface` | Prepares the URL with optional `$params` which redirects to the provider's authorization prompt and returns a PSR-7 `UriInterface` with all necessary parameters set
`getRequestAuthorization(RequestInterface $request, AccessToken $token)` | PSR-7 `RequestInterface` | Authorizes the $request with the credentials from the given `$token` and returns a PSR-7 `RequestInterface` with all necessary headers and/or parameters set (used internally)
`request(string $path, array $params = null, string $method = null, $body = null, array $headers = null)` | PSR-7 `ResponseInterface`  | Prepares an API request to `$path` with the given parameters, gets authorization, fires the request and returns a PSR-7 `ResponseInterface` with the corresponding API response
`setStorage(OAuthStorageInterface $storage)` | `OAuthInterface` (self) | Sets an optional `OAuthStorageInterface`
`setLogger(LoggerInterface $logger)` | void | from PSR-3 `LoggerAwareInterface`
`setRequestFactory(RequestFactoryInterface $requestFactory)` | `OAuthInterface` (self) | Sets an optional PSR-17 `RequestFactoryInterface`
`setStreamFactory(StreamFactoryInterface $streamFactory)` | `OAuthInterface` (self) | Sets an optional PSR-17 `StreamFactoryInterface`
`setUriFactory(UriFactoryInterface $uriFactory)` | `OAuthInterface` (self) | Sets an optional PSR-17 `UriFactoryInterface`

The following (magic) public read-only properties are available:

property | description
-------- | -----------
`$serviceName` | the classname for the current provider
`$userRevokeURL` | an optional link to the provider's user control panel where they can revoke the current token
`$apiURL` | the base URL of the provider's API to access
`$apiDocs` | a link to the provider's API docs
`$applicationURL` | a link to the API/application credential generation page
`$endpoints` | an `EndpointMapInterface` for the current provider

Additionally, the following internal (protected) properties affect a provider's functionality

property | description
-------- | -----------
`$authURL` | URL to the the provider's consent screen
`$accessTokenURL` | the provider's token exchange URL
`$revokeURL` | (optional) an URL to revoke the given access token via the provider's API
`$endpointMap` | (optional) a class FQCN of an `EndpointMapInterface` for the provider's API
`$authHeaders` | (optional) additional headers to use during authentication
`$apiHeaders` | (optional) additional headers to use during API access


### OAuth1Interface
The `OAuth1Interface` (abstract [`OAuth1Provider`](https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/src/Core/OAuth1Provider.php)) extends the `OAuthInterface` and adds the following methods and properties.

method | return | info
------ | ------ | ----
`getRequestToken()` | `AccessToken` | Obtains an OAuth1 request token and returns an `AccessToken` object for use in the authentication request.
`getAccessToken(string $token, string $verifier)` | `AccessToken` | Obtains an OAuth1 access token with the given `$token` and `$verifier` and returns an `AccessToken` object.

Protected properties:

property | description
-------- | -----------
`$requestTokenURL` | the OAuth1 request token excange URL


### OAuth2Interface
The `OAuth2Interface` (abstract [`OAuth2Provider`](https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/src/Core/OAuth2Provider.php)) extends the `OAuthInterface` and adds the following methods and properties.

method | return | info
------ | ------ | ----
`getAccessToken(string $code, string $state = null)` | `AccessToken` | Obtains an OAuth2 access token with the given `$code` and returns an `AccessToken` object. Verifies the `$state` if the provider implements the `CSRFToken` interface.
`getAuthURL(array $params = null, $scopes = null)` | PSR-7 `UriInterface`| Prepares the URL with optional `$params` and `$scopes` which redirects to the provider's authorization prompt and returns a PSR-7 `UriInterface` with all necessary parameters set.

Protected properties:

property | description
-------- | -----------
`$authMethod` | the authentication method, `OAuth2Interface::AUTH_METHOD_HEADER` (default) or `OAuth2Interface::AUTH_METHOD_QUERY`
`$authMethodHeader` | the name of the `Authorization` header in case `OAuth2Interface::AUTH_METHOD_HEADER` is used, defaults to `Bearer`
`$authMethodQuery` | the name of the querystring in case `OAuth2Interface::AUTH_METHOD_QUERY` is used, defaults to `access_token`
`$defaultScopes` | (optional) a set of scopes to use if none were provided through `getAuthURL()`
`$scopesDelimiter` | (optional) a delimiter string for the OAuth2 scopes, defaults to `' '` (space)
`$refreshTokenURL` | (optional) a refresh token exchange URL, in case it differs from `$accessTokenURL`
`$clientCredentialsTokenURL` | (optional) a client credentials token exchange URL, in case it differs from `$accessTokenURL`

The following interfaces can alter the behaviour of the `OAuth2Provider`:

#### `ClientCredentials`
The `ClientCredentials` interface indicates that the provider supports the [client credentials grant type](https://tools.ietf.org/html/rfc6749#section-4.4).

method | return
------ | ------
`getClientCredentialsToken(array $scopes = null)` | `AccessToken`

#### `CSRFToken`
The `CSRFToken` interface enables usage of the `<state>` parameter to mitigate [cross-site request forgery](https://tools.ietf.org/html/rfc6749#section-10.12) and automatically enforces it during authorization requests.

method | return
------ | ------
`checkState(string $state = null)` | `OAuth2Interface` (self)
`setState(array $params)` | array

#### `TokenRefresh`
The `TokenRefresh` interface indicates if a provider supports usage of [refresh tokens](https://tools.ietf.org/html/rfc6749#section-10.4).
The option setting `$tokenAutoRefresh` enables automatic refresh of expired tokens when using the `OAuthInterface::request()` or the PSR-18 `OAuthInterface::sendRequest()` methods to call the provider's API.

method | return
------ | ------
`refreshAccessToken(AccessToken $token = null)` | `AccessToken`


### AccessToken

The [`AccessToken`](https://github.com/chillerlan/php-oauth-core/tree/v4.x-php7.4/src/Core/AccessToken.php) is a container to keep any token related data in one place. It extends the
[`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/v4.x-php7.4/src/SettingsContainerInterface.php)
and therefore offers all of its methods.

method | return | description
------ | ------ | -----------
`__construct(iterable  $properties = null)` | - |
`setExpiry(int $expires = null)` | `AccessToken` (self) |
`isExpired()` | bool |

Public properties:

property | type | default | description
-------- | ---- | ------- | -----------
`$accessTokenSecret` | string | `null` |  OAuth1 only
`$accessToken` | string | `null` |
`$refreshToken` | string | `null` |
`$extraParams` | array | `[]` |
`$expires` | int | `AccessToken::EOL_UNKNOWN` |
`$provider` | string | `null` |

Public constants:

constant | description
-------- | -----------
`EOL_UNKNOWN` | Denotes an unknown end of life time.
`EOL_NEVER_EXPIRES` | Denotes a token which never expires
`EXPIRY_MAX` | Defines a maximum expiry period (1 year)

Inherited from `SettingsContainerInterface`:

method | return | description
------ | ------ | -----------
`__get(string $property)` | mixed | calls `$this->{'get_'.$property}($value)` if such a method exists
`__set(string $property, $value)` | void | calls `$this->{'set_'.$property}($value)` if such a method exists
`__isset(string $property)` | bool |
`__unset(string $property)` | void |
`__toString()` | string | a JSON string
`toArray()` | array |
`fromIterable(iterable $properties)` | `SettingsContainerInterface` (self) |
`toJSON(int $jsonOptions = null)` | string | accepts [JSON options constants](http://php.net/manual/json.constants.php)
`fromJSON(string $json)` | `SettingsContainerInterface` (self) |


### OAuthStorageInterface
The `OAuthStorageInterface` (abstract [`OAuthStorageAbstract`](https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/src/Storage/OAuthStorageAbstract.php)) serves for storing access tokens and auth states (CSRF) on a per-user basis.
The included implementations are intended for throwaway use during authentication or script runtime, please refer to these for implementation details:

- [`MemoryStorage`](https://github.com/chillerlan/php-oauth-core/tree/v4.x-php7.4/src/Storage/MemoryStorage.php): non-persistent, to store an existing token during script runtime and then discard it.
- [`SessionStorage`](https://github.com/chillerlan/php-oauth-core/tree/v4.x-php7.4/src/Storage/SessionStorage.php): (half-)persistent, stores a token for as long a user's session is alive, e.g. while authenticating.

An example implementation for a persistent database storage with token encryption can be found over here: [`DBStorage`](https://github.com/chillerlan/php-oauth/blob/master/src/Storage/DBStorage.php).

method | return
------ | ------
`storeAccessToken(string $service, AccessToken $token)` | bool
`getAccessToken(string $service)` | `AccessToken`
`hasAccessToken(string $service)` | bool
`clearAccessToken(string$service)` | bool
`clearAllAccessTokens()` | bool
`storeCSRFState(string $service, string $state)` | bool
`getCSRFState(string $service)` | string
`hasCSRFState(string $service)` | bool
`clearCSRFState(string $service)` | bool
`clearAllCSRFStates()` | bool
`toStorage(AccessToken $token)` | mixed
`fromStorage($data)` | `AccessToken`


### OAuthOptions
[`OAuthOptions`](https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/src/OAuthOptions.php) is a [`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/v4.x-php7.4/src/SettingsContainerInterface.php)
that uses the plug-in traits [`OAuthOptionsTrait`](https://github.com/chillerlan/php-oauth-core/blob/v4.x-php7.4/src/OAuthOptionsTrait.php)
and [`HTTPOptionsTrait`](https://github.com/chillerlan/php-httpinterface/blob/v4.x-php7.4/src/HTTPOptionsTrait.php) to provide settings for a provider.

property | type | default | description
-------- | ---- | ------- | -----------
`$key` | string | `null` | The application key (or id) given by your provider (see [supported providers](https://github.com/chillerlan/php-oauth-providers#supported-providers))
`$secret` | string | `null` | The application secret given by your provider
`$callbackURL` | string | `null` | The callback URL associated with your application
`$sessionStart` | bool | `true` | Whether or not to start the session when [session storage](https://github.com/chillerlan/php-oauth-core/tree/v4.x-php7.4/src/Storage/SessionStorage.php) is used
`$sessionTokenVar` | string | 'chillerlan-oauth-token' | The session array key for token storage
`$sessionStateVar` | string | 'chillerlan-oauth-state' | The session array key for <state> storage (OAuth2)
`$tokenAutoRefresh` | bool | `true` | Whether or not to automatically refresh access tokens (OAuth2)

from `HTTPOptionsTrait`:

property | type | default | description
-------- | ---- | ------- | -----------
`$user_agent` | string |  |
`$curl_options` | array | `[]` | https://php.net/manual/function.curl-setopt.php
`$ca_info` | string | `null` | https://curl.haxx.se/docs/caextract.html
`$ssl_verifypeer` | bool | `true` | see `CURLOPT_SSL_VERIFYPEER`
`$curl_multi_options` | array | `[]` |
`$windowSize` | int | 5 |
`$sleep` | int/float | `null` |
`$timeout` | int | 10 |
`$retries` | int | 3 |
`$curl_check_OCSP` | bool | `false` |


# Disclaimer
OAuth tokens are secrets and should be treated as such. Store them in a safe place,
[consider encryption](http://php.net/manual/book.sodium.php). <br/>
I won't take responsibility for stolen auth tokens. Use at your own risk.
