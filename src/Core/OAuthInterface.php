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
 * @property string                                         $accessTokenURL
 * @property string                                         $authURL
 * @property string                                         $apiDocs
 * @property string                                         $apiURL
 * @property string                                         $applicationURL
 * @property \chillerlan\HTTP\MagicAPI\EndpointMapInterface $endpoints
 * @property string                                         $revokeURL
 * @property string                                         $serviceName
 * @property string                                         $userRevokeURL
 */
interface OAuthInterface extends ApiClientInterface, ClientInterface, LoggerAwareInterface{

	/**
	 * @param array $params
	 *
	 * @return \Psr\Http\Message\UriInterface
	 */
	public function getAuthURL(array $params = null):UriInterface;

	/**
	 * @param \Psr\Http\Message\RequestInterface $request
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return \Psr\Http\Message\RequestInterface
	 * @internal
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken $token):RequestInterface;

	/**
	 * @param string $path
	 * @param array  $params
	 * @param string $method
	 * @param null   $body
	 * @param array  $headers
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function request(string $path, array $params = null, string $method = null, $body = null, array $headers = null):ResponseInterface;

	/**
	 * @param \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setStorage(OAuthStorageInterface $storage):OAuthInterface;

	/**
	 * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setRequestFactory(RequestFactoryInterface $requestFactory):OAuthInterface;

	/**
	 * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setStreamFactory(StreamFactoryInterface $streamFactory):OAuthInterface;

	/**
	 * @param \Psr\Http\Message\UriFactoryInterface $uriFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setUriFactory(UriFactoryInterface $uriFactory):OAuthInterface;

}
