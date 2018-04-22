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
$response = $provider->request('/some/endpoint', ['q' => 'param'], 'POST', ['data' => 'content'], ['content-type' => 'whatever']);

// use the data
$headers = $response->headers;
$data    = $response->json;
```
