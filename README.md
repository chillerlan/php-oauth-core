# chillerlan/php-oauth-core
A PHP7.2+ OAuth1/2 client with an integrated API wrapper, [loosely based](https://github.com/codemasher/PHPoAuthLib) on [Lusitanian/PHPoAuthLib](https://github.com/Lusitanian/PHPoAuthLib).

[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![Travis CI][travis-badge]][travis]
[![CodeCov][coverage-badge]][coverage]
[![Scrunitizer CI][scrutinizer-badge]][scrutinizer]
[![Packagist downloads][downloads-badge]][downloads]
[![PayPal donate][donate-badge]][donate]

[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-oauth-core.svg?style=flat-square
[packagist]: https://packagist.org/packages/chillerlan/php-oauth-core
[license-badge]: https://img.shields.io/github/license/chillerlan/php-oauth-core.svg?style=flat-square
[license]: https://github.com/chillerlan/php-oauth-core/blob/master/LICENSE
[travis-badge]: https://img.shields.io/travis/chillerlan/php-oauth-core.svg?style=flat-square
[travis]: https://travis-ci.org/chillerlan/php-oauth-core
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-oauth-core.svg?style=flat-square
[coverage]: https://codecov.io/github/chillerlan/php-oauth-core
[scrutinizer-badge]: https://img.shields.io/scrutinizer/g/chillerlan/php-oauth-core.svg?style=flat-square
[scrutinizer]: https://scrutinizer-ci.com/g/chillerlan/php-oauth-core
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-oauth-core.svg?style=flat-square
[downloads]: https://packagist.org/packages/chillerlan/php-oauth-core/stats
[donate-badge]: https://img.shields.io/badge/donate-paypal-ff33aa.svg?style=flat-square
[donate]: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WLYUNAT9ZTJZ4

# Documentation
## Requirements
- PHP 7.2+
- a [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible HTTP client library of your choice
  - optional [PSR-17](https://www.php-fig.org/psr/psr-17/) compatible Request-, Response- and UriFactories
- see [`chillerlan/php-oauth-providers`](https://github.com/chillerlan/php-oauth-providers) for already implemented providers

## Installation
**requires [composer](https://getcomposer.org)**

`composer.json` (note: replace `dev-master` with a [version boundary](https://getcomposer.org/doc/articles/versions.md))
```json
{
	"require": {
		"php": "^7.2",
		"chillerlan/php-oauth-core": "dev-master"
	}
}
```
Profit!

## API
### [`OAuthInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuthProvider.php)
The `OAuthInterface` implements the PSR-18 [`ClientInterface`](https://www.php-fig.org/psr/psr-18/) (a PSR-18 compatible http client is still required), 
the PSR-3[`LoggerAwareInterface`](https://www.php-fig.org/psr/psr-3/) as well as the 
[`ApiClientInterface`](https://github.com/chillerlan/php-magic-apiclient/blob/master/src/ApiClientInterface.php) 
and offers basic methods that are common to the OAuth 1/2 interfaces, all supplied in the abstract class [`OAuthProvider`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuthProvider.php).

method | return | description
------ | ------ | -----------
`__construct(ClientInterface $http, OAuthStorageInterface $storage, SettingsContainerInterface $options, LoggerInterface $logger = null)` | - | 
`__call(string $name, array $arguments)` | PSR-7 `ResponseInterface` | magic API
`getAuthURL(array $params = null)` | PSR-7 `UriInterface` | 
`request(string $path, array $params = null, string $method = null, $body = null, array $headers = null)` | PSR-7 `ResponseInterface`  | 
`setStorage(OAuthStorageInterface $storage)` | `OAuthInterface` | 
`setLogger(LoggerInterface $logger)` | void | PSR-3
`setRequestFactory(RequestFactoryInterface $requestFactory)` | `OAuthInterface` | PSR-17
`setStreamFactory(StreamFactoryInterface $streamFactory)` | `OAuthInterface` | PSR-17
`setUriFactory(UriFactoryInterface $uriFactory)` | `OAuthInterface` | PSR-17
`getRequestAuthorization(RequestInterface $request, AccessToken $token)` | PSR-7 `RequestInterface`

The following public properties are available:

property | description
-------- | -----------
`$serviceName` | the classname for the current provider
`$userRevokeURL` | an optional link to the provider's user control panel where they can revoke the current token
`$apiDocs` | a link to the provider's API docs
`$applicationURL` | a link to the API/application credential generation page
`$endpoints` | an `EndpointMapInterface` for the current provider

The following internal (protected) properties affect a provider's functionality

property | description
-------- | -----------
`$authURL` | URL to the the provider's consent screen
`$accessTokenURL` | the provider's token exchange URL
`$apiURL` | the base URL of the provider's API to access
`$revokeURL` | (optional) an URL to revoke the given access token via the provider's API
`$endpointMap` | (optional) a class FQCN of an `EndpointMapInterface` for the provider's API
`$authHeaders` | (optional) additional headers to use during authentication
`$apiHeaders` | (optional) additional headers to use during API access


### [`OAuth1Interface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuth1Provider.php)

method | return
------ | ------
`getRequestToken()` | `AccessToken`
`getAccessToken(string $token, string $verifier)` | `AccessToken`

Protected properties:

property | description
-------- | -----------
`$requestTokenURL` | the OAuth1 request token excange URL

#### A minimal OAuth1 provider
```php
use chillerlan\OAuth\Core\OAuth1Provider;

class MyOauth1Provider extends Oauth1Provider{

	protected $requestTokenURL = 'https://example.com/oauth/request_token';
	protected $authURL         = 'https://example.com/oauth/authorize';
	protected $accessTokenURL  = 'https://example.com/oauth/access_token';
	protected $apiURL          = 'https://api.example.com';

}
```


### [`OAuth2Interface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuth2Provider.php) 

method | return
------ | ------
`getAccessToken(string $code, string $state = null)` | `AccessToken`
`getAuthURL(array $params = null, $scopes = null)` | PSR-7 `UriInterface`

Protected properties:

property | description
-------- | -----------
`$authMethod` | the authentication method, `OAuth2Interface::AUTH_METHOD_HEADER` (default) or `OAuth2Interface::AUTH_METHOD_QUERY`
`$authMethodHeader` | the name of the `Authorization` header in case `OAuth2Interface::AUTH_METHOD_HEADER` is used, defaults to `Bearer`
`$authMethodQuery` | the name of the querystring in case `OAuth2Interface::AUTH_METHOD_QUERY` is used, defaults to `access_token`
`$scopesDelimiter` | (optional) a delimiter string for the OAuth2 scopes, defaults to `' '` (space)
`$refreshTokenURL` | (optional) a refresh token exchange URL, in case it differs from `$accessTokenURL`
`$clientCredentialsTokenURL` | (optional) a client credentials token exchange URL, in case it differs from `$accessTokenURL`

#### `ClientCredentials`
The `ClientCredentials` interface indicates that the provider supports the [client credentials grant type](https://tools.ietf.org/html/rfc6749#section-4.4).

method | return
------ | ------
`getClientCredentialsToken(array $scopes = null)` | `AccessToken`

#### `CSRFToken`
The `CSRFToken` interface enables usage of the `<state>` parameter to mitigate [cross-site request forgery](https://tools.ietf.org/html/rfc6749#section-10.12) and automatically enforces it during authorization requests.

method | return
------ | ------
(protected) `checkState(string $state = null)` | `OAuth2Interface`
(protected) `setState(array $params)` | array

#### `TokenRefresh`
The `TokenRefresh` interface indicates if a provider supports usage of [refresh tokens](https://tools.ietf.org/html/rfc6749#section-10.4). 
The option setting `$tokenAutoRefresh` enables automatic refresh of expired tokens when using the `OAuthInterface::request()` or the PSR-18 `OAuthInterface::sendRequest()` methods to call the provider's API.

method | return
------ | ------
`refreshAccessToken(AccessToken $token = null)` | `AccessToken`

#### A minimal OAuth2 provider
```php
use chillerlan\OAuth\Core\OAuth2Provider;

class MyOauth2Provider extends Oauth2Provider implements ClientCredentials, CSRFToken, TokenRefresh{

	protected $authURL                   = 'https://example.com/oauth2/authorize';
	protected $accessTokenURL            = 'https://example.com/oauth2/token';
	protected $apiURL                    = 'https://api.example.com';

	// optional
	protected $clientCredentialsTokenURL = 'https://example.com/oauth2/client_credentials';
	protected $authMethod                = self::AUTH_METHOD_HEADER;
	protected $authMethodHeader          = 'OAuth';
	protected $scopesDelimiter           = ',';
}
```


### [`OAuthStorageInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Storage/OAuthStorageAbstract.php)

The `OAuthStorageInterface` serves for storing access tokens and auth states (CSRF) on a per-user basis.
The included implementations are intended for throwaway use during authentication or script runtime, please refer to these for implementation details (extend `OAuthStorageAbstract`):

- [`MemoryStorage`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/MemoryStorage.php): non-persistent, to store an existing token during script runtime and then discard it.
- [`SessionStorage`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/SessionStorage.php): (half-)persistent, stores a token for as long a user's session is alive, e.g. while authenticating.

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

### [`AccessToken`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Core/AccessToken.php)
The `AccessToken` is a container to keep any token related data in one place. It extends the 
[`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/master/src/SettingsContainerInterface.php)
and therefore offers all of its methods.

method | return | description
------ | ------ | -----------
`__construct(iterable  $properties = null)` | - |
`setExpiry(int $expires = null)` | `AccessToken` |
`isExpired()` | bool |

inherited from `SettingsContainerInterface`:

method | return | description
------ | ------ | -----------
`__get(string $property)` | mixed | calls `$this->{'get_'.$property}($value)` if such a method exists
`__set(string $property, $value)` | void | calls `$this->{'set_'.$property}($value)` if such a method exists
`__isset(string $property)` | bool | 
`__unset(string $property)` | void | 
`__toString()` | string | a JSON string
`toArray()` | array | 
`fromIterable(iterable $properties)` | `SettingsContainerInterface` | 
`toJSON(int $jsonOptions = null)` | string | accepts [JSON options constants](http://php.net/manual/json.constants.php)
`fromJSON(string $json)` | `SettingsContainerInterface` | 

property | type | default | description
-------- | ---- | ------- | -----------
`$accessTokenSecret` | string | `null` |  OAuth1 only
`$accessToken` | string | `null` |
`$refreshToken` | string | `null` |
`$extraParams` | array | `[]` |
`$expires` | int | `AccessToken::EOL_UNKNOWN` |
`$provider` | string | `null` |

### [`OAuthOptions`](https://github.com/chillerlan/php-oauth-core/blob/master/src/OAuthOptions.php)
`OAuthOptions` is a [`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/master/src/SettingsContainerInterface.php)
that uses the plug-in traits [`OAuthOptionsTrait`](https://github.com/chillerlan/php-oauth-core/blob/master/src/OAuthOptionsTrait.php) 
and [`HTTPOptionsTrait`](https://github.com/chillerlan/php-httpinterface/blob/master/src/HTTPOptionsTrait.php) to provide settings for a provider.

property | type | default | description
-------- | ---- | ------- | -----------
`$key` | string | `null` | The application key (or id) given by your provider (see [supported providers](https://github.com/chillerlan/php-oauth-providers#supported-providers))
`$secret` | string | `null` | The application secret given by your provider
`$callbackURL` | string | `null` | The callback URL associated with your application
`$sessionStart` | bool | `true` | Whether or not to start the session when [session storage](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/SessionStorage.php) is used
`$sessionTokenVar` | string | 'chillerlan-oauth-token' | The session array key for token storage 
`$sessionStateVar` | string | 'chillerlan-oauth-state' | The session array key for <state> storage (OAuth2) 
`$tokenAutoRefresh` | bool | `true` | Whether or not to automatically refresh access tokens (OAuth2)

from `HTTPOptionsTrait`:

property | type | default | description
-------- | ---- | ------- | -----------
`$user_agent` | string |  |  
`$curl_options` | array | `[]` | https://php.net/manual/function.curl-setopt.php
`$ca_info` | string | `null` | https://curl.haxx.se/docs/caextract.html 
`$ssl_verifypeer` | bool | `true` | see CURLOPT_SSL_VERIFYPEER  
`$curlHandle` | string | `CurlHandle::class` |
`$windowSize` | int | 5 |
`$sleep` | int/float | `null` |
`$timeout` | int | 10 |
`$retries` | int | 3 |
`$curl_multi_options` | array | `[]` |

## Testing

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

			"chillerlan\\HTTPTest\\MagicAPI\\": "vendor/chillerlan/php-magic-apiclient/tests",
			"chillerlan\\OAuthTest\\": "vendor/chillerlan/php-oauth-core/tests"
		}
	}
}
```

The [basic unit test](https://github.com/chillerlan/php-oauth-core/tree/master/tests/Providers) looks as follows:
```php
namespace my\project\test;

use my\project\source\MyProvider;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \my\project\source\MyProvider $provider
 */
class MyProviderTest extends OAuth2ProviderTestAbstract{

	protected $CFG = __DIR__.'/../config'; // specifies the path for .env, cacert.pem and oauth tokens
	protected $FQN = MyProvider::class;

}
```
That is all! You'll only need to add/overload methods in case you need to cover edge cases or additional features of your provider implementation.

In case you want to run tests against a live API, you can use the [abstract API tests](https://github.com/chillerlan/php-oauth-core/tree/master/tests/API).
The live API tests are disabled on (Travis) CI and you need to enable them explicit by changing the line `IS_CI=TRUE` in you project's .env.
```php
namespace my\project\test;

use my\project\source\MyProvider;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \my\project\source\MyProvider $provider
 */
class MyProviderAPITest extends OAuth2APITestAbstract{

	protected $CFG = __DIR__.'/../config';
	protected $FQN = MyProvider::class;
	protected $ENV = 'MYPROVIDER'; // the prefix for the provider's name in .env

	// your live API tests here
	public function testIdentity(){
		$r = $this->provider->identity();
		$this->assertSame($this->testuser, $this->responseJson($r)->id);
	}

}
```

# Disclaimer
OAuth tokens are secrets and should be treated as such. Store them in a safe place,
[consider encryption](http://php.net/manual/book.sodium.php).<br/>
I won't take responsibility for stolen auth tokens. Use at your own risk.
