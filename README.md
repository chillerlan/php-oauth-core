# chillerlan/php-oauth-core

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
- see [`chillerlan/php-oauth`](https://github.com/chillerlan/php-oauth) for already implemented providers

## Getting Started
In order to instance an [`OAuthInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuthInterface.php) you you'll need to invoke a PSR-18 [`ClientInterface`](https://github.com/php-fig/http-client/blob/master/src/ClientInterface.php), a [`OAuthStorageInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Storage/OAuthStorageInterface.php) and `OAuthOptions` (a [`SettingsContainerInterface`](https://github.com/chillerlan/php-settings-container/blob/master/src/SettingsContainerInterface.php)) objects first:
```php
use chillerlan\OAuth\Providers\<PROVIDER_NAMESPACE>\<PROVIDER>;
use chillerlan\OAuth\{OAuthOptions, Storage\SessionTokenStorage};
use <PSR-18 HTTP Client>;

// OAuthOptions
$options = new OAuthOptions([
	// OAuthOptions
	'key'         => '<API_KEY>',
	'secret'      => '<API_SECRET>',
	'callbackURL' => '<API_CALLBACK_URL>',
]);

// a \Psr\Http\Client\ClientInterface
$http = new HttpClient;

// OAuthStorageInterface
// a persistent storage is required for authentication!
$storage = new SessionTokenStorage($options);

// an optional \Psr\LoggerInterface logger
$logger = new Logger;

// invoke and use the OAuthInterface
$provider = new Provider($http, $storage, $options, $logger);
```

## Authentication
The application flow may differ slightly depending on the provider; there's a working authentication example in the provider repository.

### Step 1: optional login link
Display a login link and provide the user with information what kind of data you're about to access; ask them for permission to save the access token if needed.

```php
echo '<a href="?login='.$provider->serviceName.'">connect with '.$provider->serviceName.'!</a>';
```

### Step 2: redirect to the provider
Redirect to the provider's login screen with optional arguments in the authentication URL, like permissions, scopes etc.
```php
// optional scopes for OAuth2 providers
$scopes = [
	Provider::SCOPE_WHATEVER,
];

if(isset($_GET['login']) && $_GET['login'] === $provider->serviceName){
	header('Location: '.$provider->getAuthURL(['extra-param' => 'val'], $scopes));
}
```

### Step 3: receive the token
Receive the access token, save it, do whatever you need to do, then redirect to step 4

#### OAuth1
```php
if(isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){
	$token = $provider->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

	// save & redirect...
}
```

#### OAuth2
usage of the `<state>` parameter depends on the provider
```php
if(isset($_GET['code'])){
	$token = $provider->getAccessToken($_GET['code'], $_GET['state'] ?? null);

	// save & redirect...
}
```

### Step 4: auth Granted
After receiving the access token, go on and verify it then use the API.
```php
if(isset($_GET['granted']) && $_GET['granted'] === $provider->serviceName){
	$response = $provider->doStuff();
	
	// ...
}
```

## Call the Provider's API
After successfully receiving the Token, we're ready to make API requests:
```php
// import a token to the OAuth token storage if needed
$storage->storeAccessToken($provider->serviceName, new AccessToken->__fromJSON($token_json));

// make a request
$response = $provider->request(
	'/some/endpoint', 
	['q' => 'param'], 
	'POST', 
	['data' => 'content'], 
	['content-type' => 'whatever']
);

// use the data: $response is a PSR-7 ResponseInterface
$headers = $response->getHeaders();
$data    = $response->getBody()->getContents();
```

## Extensions
In order to use a provider or storage, that is not yet supported, you'll need to implement the respective interfaces:

### [`OAuth1Interface`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Core/OAuth1Provider.php)
The OAuth1 implementation is close to Twitter's specs and *should* work for most other OAuth1 services.

```php
use chillerlan\OAuth\Core\OAuth1Provider;

class MyOauth1Provider extends Oauth1Provider{

	protected $apiURL          = 'https://api.example.com';
	protected $requestTokenURL = 'https://example.com/oauth/request_token';
	protected $authURL         = 'https://example.com/oauth/authorize';
	protected $accessTokenURL  = 'https://example.com/oauth/access_token';

}
```

### [`OAuth2Interface`](https://github.com/chillerlan/php-oauth/tree/master/src/Providers/OAuth2Provider.php)
[OAuth2 is a very straightforward... mess](https://hueniverse.com/oauth-2-0-and-the-road-to-hell-8eec45921529). Please refer to your provider's docs for implementation details.
```php
use chillerlan\OAuth\Core\OAuth2Provider;

class MyOauth2Provider extends Oauth2Provider implements ClientCredentials, CSRFToken, TokenExpires, TokenRefresh{
	use OAuth2ClientCredentialsTrait, OAuth2CSRFTokenTrait, OAuth2TokenRefreshTrait;

	public const SCOPE_WHATEVER = 'whatever';

	protected $apiURL                    = 'https://api.example.com';
	protected $authURL                   = 'https://example.com/oauth2/authorize';
	protected $accessTokenURL            = 'https://example.com/oauth2/token';
	protected $clientCredentialsTokenURL = 'https://example.com/oauth2/client_credentials';
	protected $authMethod                = self::HEADER_BEARER;
	protected $authHeaders               = ['Accept' => 'application/json'];
	protected $apiHeaders                = ['Accept' => 'application/json'];
	protected $scopesDelimiter           = ',';

}
```

### [`OAuthStorageInterface`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/OAuthStorageInterface.php)
There are 2 different `OAuthStorageInterface`, refer to these for implementation details (extend `OAuthStorageAbstract`):
- [`MemoryStorage`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/MemoryStorage.php): non-persistent, to store an existing token during script runtime and then discard it.
- [`SessionStorage`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/SessionStorage.php): (half-)persistent, stores a token for as long a user's session is alive, e.g. while authenticating.

## API
### [`OAuthInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuthProvider.php)
Implements PSR-18 [`ClientInterface`](https://www.php-fig.org/psr/psr-18/) and PSR-3[`LoggerAwareInterface`](https://www.php-fig.org/psr/psr-3/).

method | return
------ | ------
`__construct(ClientInterface $http, OAuthStorageInterface $storage, SettingsContainerInterface $options)` | -
`getAuthURL(array $params = null)` | PSR-7 `UriInterface`
`request(string $path, array $params = null, string $method = null, $body = null, array $headers = null)` | PSR-7 `ResponseInterface` 
`setRequestFactory(RequestFactoryInterface $requestFactory)` | `OAuthInterface`
`setStreamFactory(StreamFactoryInterface $streamFactory)` | `OAuthInterface`
`setUriFactory(UriFactoryInterface $uriFactory)` | `OAuthInterface`

property | description
-------- | -----------
`$serviceName` | the classname for the current provider
`$accessTokenURL` | the provider`s access token URL 
`$authURL` |  the provider`s authentication token URL 
`$revokeURL` | an optional URL to revoke an access token (via API)
`$userRevokeURL` | an optional link to the provider's user control panel where they can revoke the current token

### [`OAuth1Interface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuth1Provider.php)
method | return
------ | ------
`getAccessToken(string $token, string $verifier, string $tokenSecret = null)` | `AccessToken`
`getRequestToken()` | `AccessToken`

### [`OAuth2Interface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuth2Provider.php)
method | return
------ | ------
`getAccessToken(string $code, string $state = null)` | `AccessToken`
`getAuthURL(array $params = null, $scopes = null)` | PSR-7 `UriInterface`

### `ClientCredentials`
implemented by `OAuth2ClientCredentialsTrait`

method | return
------ | ------
`getClientCredentialsToken(array $scopes = null)` | `AccessToken`

### `CSRFToken`
implemented by `OAuth2CSRFTokenTrait`

method | return
------ | ------
(protected) `checkState(string $state = null)` | `OAuth2Interface`
(protected) `setState(array $params)` | array

### `TokenRefresh`
implemented by `OAuth2TokenRefreshTrait`

method | return
------ | ------
`refreshAccessToken(AccessToken $token = null)` | `AccessToken`

### `TokenExpires`
method | return
------ | ------

### [`OAuthStorageInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Storage/OAuthStorageAbstract.php)
method | return
------ | ------
`storeAccessToken(string $service, AccessToken $token)` | `OAuthStorageInterface`
`getAccessToken(string $service)` | `AccessToken`
`hasAccessToken(string $service)` | `AccessToken`
`clearAccessToken(string$service)` | `OAuthStorageInterface`
`clearAllAccessTokens()` | `OAuthStorageInterface`
`storeCSRFState(string $service, string $state)` | `OAuthStorageInterface`
`getCSRFState(string $service)` | string
`hasCSRFState(string $service)` | bool
`clearCSRFState(string $service)` | `OAuthStorageInterface`
`clearAllCSRFStates()` | `OAuthStorageInterface`
`toStorage(AccessToken $token)` | string
`fromStorage(string $data)` | `AccessToken`

### [`AccessToken`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Core/AccessToken.php)
method | return | description
------ | ------ | -----------
`__construct(array $properties = null)` | - |
`__set(string $property, $value)` | void | overrides `chillerlan\Traits\Container`
`setExpiry(int $expires = null)` | `AccessToken` |
`isExpired()` | `bool` |

property | type | default | allowed | description
-------- | ---- | ------- | ------- | -----------
`$requestToken` | string | null | * | OAuth1 only
`$requestTokenSecret` | string | null | * | OAuth1 only
`$accessTokenSecret` | string | null | * | OAuth1 only
`$accessToken` | string | null | * |
`$refreshToken` | string | null | * |
`$extraParams` | array | `[]` |  |
`$expires` | int | `AccessToken::EOL_UNKNOWN` |  |
`$provider` | string | null | * |

# Disclaimer
OAuth tokens are secrets and should be treated as such. Store them in a safe place,
[consider encryption](http://php.net/manual/book.sodium.php).<br/>
I won't take responsibility for stolen auth tokens. Use at your own risk.
