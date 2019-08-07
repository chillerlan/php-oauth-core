<?php
/**
 * Interface OAuthInterface
 *
 * @filesource   OAuthInterface.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\MagicAPI\ApiClientInterface;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Http\Message\{
	RequestFactoryInterface, RequestInterface, ResponseInterface,
	StreamFactoryInterface, UriFactoryInterface, UriInterface
};

/**
 * @property string                                         $apiDocs
 * @property string                                         $apiURL
 * @property string                                         $applicationURL
 * @property \chillerlan\HTTP\MagicAPI\EndpointMapInterface $endpoints
 * @property string                                         $serviceName
 * @property string                                         $userRevokeURL
 */
interface OAuthInterface extends ApiClientInterface, ClientInterface, LoggerAwareInterface{

	/**
	 * Prepares the URL with optional $params which redirects to the provider's authorization prompt
	 * and returns a PSR-7 UriInterface with all necessary parameters set
	 *
	 * @param array $params
	 *
	 * @return \Psr\Http\Message\UriInterface
	 */
	public function getAuthURL(array $params = null):UriInterface;

	/**
	 * Authorizes the $request with the credentials from the given $token
	 * and returns a PSR-7 RequestInterface with all necessary headers and/or parameters set
	 *
	 * @param \Psr\Http\Message\RequestInterface $request
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return \Psr\Http\Message\RequestInterface
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 * @internal
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken $token):RequestInterface;

	/**
	 * Prepares an API request to $path with the given parameters, gets authorization, fires the request
	 * and returns a PSR-7 ResponseInterface with the corresponding API response
	 *
	 * @param string $path
	 * @param array  $params
	 * @param string $method
	 * @param null   $body
	 * @param array  $headers
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function request(string $path, array $params = null, string $method = null, $body = null, array $headers = null):ResponseInterface;

	/**
	 * Sets an optional OAuthStorageInterface
	 *
	 * @param \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setStorage(OAuthStorageInterface $storage):OAuthInterface;

	/**
	 * Sets an optional PSR-17 RequestFactoryInterface
	 *
	 * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setRequestFactory(RequestFactoryInterface $requestFactory):OAuthInterface;

	/**
	 * Sets an optional PSR-17 StreamFactoryInterface
	 *
	 * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setStreamFactory(StreamFactoryInterface $streamFactory):OAuthInterface;

	/**
	 * Sets an optional PSR-17 UriFactoryInterface
	 *
	 * @param \Psr\Http\Message\UriFactoryInterface $uriFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setUriFactory(UriFactoryInterface $uriFactory):OAuthInterface;

}
