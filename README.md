# chillerlan/php-oauth-core

[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![Travis CI][travis-badge]][travis]
[![CodeCov][coverage-badge]][coverage]
[![Scrunitizer CI][scrutinizer-badge]][scrutinizer]
[![Gemnasium][gemnasium-badge]][gemnasium]
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
[gemnasium-badge]: https://img.shields.io/gemnasium/chillerlan/php-oauth-core.svg?style=flat-square
[gemnasium]: https://gemnasium.com/github.com/chillerlan/php-oauth-core
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-oauth-core.svg?style=flat-square
[downloads]: https://packagist.org/packages/chillerlan/php-oauth-core/stats
[donate-badge]: https://img.shields.io/badge/donate-paypal-ff33aa.svg?style=flat-square
[donate]: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WLYUNAT9ZTJZ4

# Documentation
## Requirements
- PHP 7.2+
- the [Sodium](http://php.net/manual/book.sodium.php) extension for token encryption
- cURL, PHP's stream wrapper or a HTTP client library of your choice
- see [`chillerlan/php-oauth`](https://github.com/chillerlan/php-oauth)

## Getting Started
In order to instance an [`OAuthInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuthInterface.php) you you'll need to invoke a [`HTTPClientInterface`](https://github.com/chillerlan/php-httpinterface/blob/master/src/HTTPClientInterface.php), [`OAuthStorageInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Storage/OAuthStorageInterface.php) and `OAuthOptions` (a [`ContainerInterface`](https://github.com/chillerlan/php-traits/blob/master/src/ContainerInterface.php)) objects first:
```php
use chillerlan\OAuth\Providers\<PROVIDER_NAMESPACE>\<PROVIDER>;
use chillerlan\OAuth\{
	OAuthOptions, Storage\SessionTokenStorage
};
use chillerlan\HTTP\CurlClient;

// OAuthOptions
$options = new OAuthOptions([
	// OAuthOptions
	'key'         => '<API_KEY>',
	'secret'      => '<API_SECRET>',
	'callbackURL' => '<API_CALLBACK_URL>',

	// HTTPOptions
	'ca_info'          => '/path/to/cacert.pem', // https://curl.haxx.se/ca/cacert.pem
	'userAgent'        => 'my-awesome-oauth-app',
]);

// HTTPClientInterface
$http = new CurlClient($options);

// OAuthStorageInterface
// a persistent storage is required for authentication!
$storage = new SessionTokenStorage($options);

// optional scopes for OAuth2 providers
$scopes = [
	Provider::SCOPE_WHATEVER,
];

// an optional \Psr\LoggerInterface logger
$logger = new Logger;

// invoke and use the OAuthInterface
$provider = new Provider($http, $storage, $options, $logger, $scopes);
```

## Authentication
The application flow may differ slightly depending on the provider; there's a working authentication example in the provider repository.

### Step 1: optional login link
Display a login link and provide the user with information what kind of data you're about to access; ask them for permission to save the access token if needed.

```php
echo '<a href="?login='.$provider->serviceName.'">connect with '.$provider->serviceName.'!</a>';
```

### Step 2: redirect to the provider
Redirect to the provider's login screen with optional arguments in the authentication URL, like permissions etc.
```php
if(isset($_GET['login']) && $_GET['login'] === $provider->serviceName){
	header('Location: '.$provider->getAuthURL(['extra-param' => 'val']));
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

// use the data
$headers = $response->headers;
$data    = $response->json;
```

## Extensions
In order to use a provider or storage, that is not yet supported, you'll need to implement the respective interfaces:

### [`OAuth1Interface`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Core/OAuth1Provider.php)
The OAuth1 implementation is close to Twitter's specs and *should* work for most other OAuth1 services.

```php
use chillerlan\OAuth\Providers\OAuth1Provider;

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
use chillerlan\OAuth\Providers\OAuth2Provider;

class MyOauth2Provider extends Oauth2Provider implements ClientCredentials, CSRFToken, TokenExpires, TokenRefresh{
	use OAuth2ClientCredentialsTrait, CSRFTokenTrait, OAuth2TokenRefreshTrait;

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
There are currently 3 different `OAuthStorageInterface`, refer to these for implementation details (extend `OAuthStorageAbstract`):
- [`MemoryStorage`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/MemoryStorage.php): non-persistent, to store a token during script runtime and then discard it.
- [`SessionStorage`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/SessionStorage.php): half-persistent, stores a token for as long a user's session is alive.
- [`DBStorage`](https://github.com/chillerlan/php-oauth-core/tree/master/src/Storage/DBStorage.php): persistent, multi purpose database driven storage with encryption support

## API
### [`OAuthInterface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuthProvider.php)
method | return
------ | ------
`__construct(HTTPClientInterface $http, OAuthStorageInterface $storage, ContainerInterface $options, LoggerInterface $logger = null)` | -
`getAuthURL(array $params = null)` | string
`getStorageInterface()` | `OAuthStorageInterface`
`request(string $path, array $params = null, string $method = null, $body = null, array $headers = null)` | `HTTPResponseInterface`

property | description
-------- | -----------
`$serviceName` | the classname for the current provider
`$userRevokeURL` | an optional link to the provider's user control panel where they can revoke the current token

### [`OAuth1Interface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuth1Provider.php)
method | return
------ | ------
`getAccessToken(string $token, string $verifier, string $tokenSecret = null)` | `AccessToken`
`getRequestToken()` | `AccessToken`
`getSignature(string $url, array $params, string $method = null)` | string

### [`OAuth2Interface`](https://github.com/chillerlan/php-oauth-core/blob/master/src/Core/OAuth2Provider.php)
method | return
------ | ------
`__construct(HTTPClientInterface $http, OAuthStorageInterface $storage, ContainerInterface $options, LoggerInterface $logger = null, array $scopes = null)` | -
`getAccessToken(string $code, string $state = null)` | `AccessToken`

### `ClientCredentials`
implemented by `OAuth2ClientCredentialsTrait`

method | return
------ | ------
`getClientCredentialsToken(array $scopes = null)` | `AccessToken`
(protected) `getClientCredentialsTokenBody(array $scopes)` | array
(protected) `getClientCredentialsTokenHeaders()` | array

### `CSRFToken`
implemented by `CSRFTokenTrait`

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
`__toArray()` | array | from `chillerlan\Traits\Container`
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

# Disclaimer
OAuth tokens are secrets and should be treated as such. Store them in a safe place,
[consider encryption](http://php.net/manual/book.sodium.php).<br/>
I won't take responsibility for stolen auth tokens. Use at your own risk.
