<?php
/**
 * Interface OAuthInterface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\OAuth\Storage\OAuthStorageInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Http\Message\{
	RequestFactoryInterface, RequestInterface, ResponseInterface,
	StreamFactoryInterface, StreamInterface, UriFactoryInterface, UriInterface
};

/**
 * Specifies the basic methods for an OAuth provider.
 *
 * @property string $apiDocs
 * @property string $apiURL
 * @property string $applicationURL
 * @property string $serviceName
 * @property string $userRevokeURL
 */
interface OAuthInterface extends ClientInterface, LoggerAwareInterface{

	/**
	 * Prepares the URL with optional $params which redirects to the provider's authorization prompt
	 * and returns a PSR-7 UriInterface with all necessary parameters set
	 */
	public function getAuthURL(array $params = null):UriInterface;

	/**
	 * Authorizes the $request with the credentials from the given $token
	 * and returns a PSR-7 RequestInterface with all necessary headers and/or parameters set
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 * @internal
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken $token):RequestInterface;

	/**
	 * Prepares an API request to $path with the given parameters, gets authorization, fires the request
	 * and returns a PSR-7 ResponseInterface with the corresponding API response
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function request(
		string $path,
		array $params = null,
		string $method = null,
		StreamInterface|array|string $body = null,
		array $headers = null
	):ResponseInterface;

	/**
	 * Sets an optional OAuthStorageInterface
	 */
	public function setStorage(OAuthStorageInterface $storage):OAuthInterface;

	/**
	 * Returns the current OAuthStorageInterface
	 */
	public function getStorage():OAuthStorageInterface;

	/**
	 * Sets an access token in the current OAuthStorageInterface (shorthand/convenience)
	 */
	public function storeAccessToken(AccessToken $token):OAuthInterface;

	/**
	 * Sets an optional PSR-17 RequestFactoryInterface
	 */
	public function setRequestFactory(RequestFactoryInterface $requestFactory):OAuthInterface;

	/**
	 * Sets an optional PSR-17 StreamFactoryInterface
	 */
	public function setStreamFactory(StreamFactoryInterface $streamFactory):OAuthInterface;

	/**
	 * Sets an optional PSR-17 UriFactoryInterface
	 */
	public function setUriFactory(UriFactoryInterface $uriFactory):OAuthInterface;

	/**
	 * Returns information about the currently authenticated user (usually a /me or /user endpoint).
	 * Throws a ProviderException if no such information is available or if the method cannot be implemnted.
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function me():ResponseInterface;

}
