<?php
/**
 * Class OAuthProvider
 *
 * @filesource   OAuthProvider.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\{HTTPClientInterface, Psr17\RequestFactory, Psr17\StreamFactory, Psr17\UriFactory, Psr7};
use chillerlan\HTTP\MagicAPI\{ApiClientException, ApiClientInterface, EndpointMapInterface};
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Message\{RequestFactoryInterface, StreamFactoryInterface, StreamInterface, UriFactoryInterface};
use Psr\Http\Message\ResponseInterface;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, NullLogger};
use ReflectionClass;

/**
 * @property string $accessTokenURL
 * @property string $authURL
 * @property string $revokeURL
 * @property string $serviceName
 * @property string $userRevokeURL
 */
abstract class OAuthProvider implements OAuthInterface, ApiClientInterface, LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * @var \chillerlan\HTTP\HTTPClientInterface
	 */
	protected $http;

	/**
	 * @var \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\HTTP\MagicAPI\EndpointMapInterface
	 */
	protected $endpoints;

	/**
	 * @var \Psr\Http\Message\RequestFactoryInterface
	 */
	protected $requestFactory;

	/**
	 * @var \Psr\Http\Message\StreamFactoryInterface
	 */
	protected $streamFactory;

	/**
	 * @var \Psr\Http\Message\UriFactoryInterface
	 */
	protected $uriFactory;

	/**
	 * @var string
	 */
	protected $serviceName;

	/**
	 * @var string
	 */
	protected $authURL;

	/**
	 * @var string
	 */
	protected $apiURL;

	/**
	 * @var string
	 */
	protected $userRevokeURL;

	/**
	 * @var string
	 */
	protected $revokeURL;

	/**
	 * @var string
	 */
	protected $accessTokenURL;

	/**
	 * @var string FQCN
	 */
	protected $endpointMap;

	/**
	 * @var array
	 */
	protected $authHeaders = [];

	/**
	 * @var array
	 */
	protected $apiHeaders = [];

	/**
	 * OAuthProvider constructor.
	 *
	 * @param \chillerlan\HTTP\HTTPClientInterface            $http
	 * @param \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
	 * @param \chillerlan\Settings\SettingsContainerInterface $options
	 *
	 * @throws \chillerlan\HTTP\MagicAPI\ApiClientException
	 */
	public function __construct(HTTPClientInterface $http, OAuthStorageInterface $storage, SettingsContainerInterface $options){
		$this->http    = $http;
		$this->storage = $storage;
		$this->options = $options;

		$this->logger         = new NullLogger;
		$this->requestFactory = new RequestFactory;
		$this->streamFactory  = new StreamFactory;
		$this->uriFactory     = new UriFactory;

		$this->serviceName = (new ReflectionClass($this))->getShortName();

		if(!empty($this->endpointMap) && class_exists($this->endpointMap)){
			$this->endpoints = new $this->endpointMap;

			if(!$this->endpoints instanceof EndpointMapInterface){
				throw new ApiClientException('invalid endpoint map');
			}

		}

	}

	/**
	 * @param string $name
	 *
	 * @return string|null
	 */
	public function __get(string $name){

		if(!in_array($name, ['serviceName', 'authURL', 'accessTokenURL', 'revokeURL', 'userRevokeURL'], true)){
			return null;
		}

		return $this->{$name};
	}

	/**
	 * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setRequestFactory(RequestFactoryInterface $requestFactory):OAuthInterface{
		$this->requestFactory = $requestFactory;

		return $this;
	}

	/**
	 * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setStreamFactory(StreamFactoryInterface $streamFactory):OAuthInterface{
		$this->streamFactory = $streamFactory;

		return $this;
	}

	/**
	 * @param \Psr\Http\Message\UriFactoryInterface $uriFactory
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	public function setUriFactory(UriFactoryInterface $uriFactory):OAuthInterface{
		$this->uriFactory = $uriFactory;

		return $this;
	}

	/**
	 * ugly, isn't it?
	 *
	 * @todo WIP
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \chillerlan\HTTP\MagicAPI\ApiClientException
	 */
	public function __call(string $name, array $arguments):ResponseInterface{

		if(!$this->endpoints instanceof EndpointMapInterface || !$this->endpoints->__isset($name)){
			throw new ApiClientException('endpoint not found');
		}

		$m = $this->endpoints->{$name};

		$endpoint      = $m['path'];
		$method        = $m['method'] ?? 'GET';
		$body          = [];
		$headers       = isset($m['headers']) && is_array($m['headers']) ? $m['headers'] : [];
		$path_elements = $m['path_elements'] ?? [];
		$params_in_url = count($path_elements);
		$params        = $arguments[$params_in_url] ?? [];
		$urlparams     = array_slice($arguments,0 , $params_in_url);

		if($params_in_url > 0){

			if(count($urlparams) < $params_in_url){
				throw new APIClientException('too few URL params, required: '.implode(', ', $path_elements));
			}

			$endpoint = sprintf($endpoint, ...$urlparams);
		}

		if(in_array($method, ['POST', 'PATCH', 'PUT', 'DELETE'])){
			$body = $arguments[$params_in_url + 1] ?? $params;

			if($params === $body){
				$params = [];
			}

			$body = Psr7\clean_query_params($body);
		}

		$params = Psr7\clean_query_params($params);

		$this->logger->debug('OAuthProvider::__call() -> '.(new ReflectionClass($this))->getShortName().'::'.$name.'()', [
			'$endpoint' => $endpoint, '$params' => $params, '$method' => $method, '$body' => $body, '$headers' => $headers,
		]);

		return $this->request($endpoint, $params, $method, $body, $headers);
	}

	/**
	 * @param string $path
	 * @param array  $params
	 * @param string $method
	 * @param null   $body
	 * @param array  $headers
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function request(string $path, array $params = null, string $method = null, $body = null, array $headers = null):ResponseInterface{
		$token = $this->storage->getAccessToken($this->serviceName);

		// attempt to refresh an expired token
		if($this instanceof TokenRefresh && $this->options->tokenAutoRefresh && ($token->isExpired() || $token->expires === $token::EOL_UNKNOWN)){
			$token = $this->refreshAccessToken($token);
		}

		$request = $this->requestFactory
			->createRequest($method ?? 'GET', Psr7\merge_query($this->apiURL.$path, $params));

		foreach(array_merge($this->apiHeaders, $headers) as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		$request = $this->getRequestAuthorization($request, $token);

		if(is_array($body) && $request->hasHeader('content-type')){
			$contentType = strtolower($request->getHeader('content-type'));

			if($contentType === 'application/x-www-form-urlencoded'){
				$body = $this->streamFactory->createStream(http_build_query($body, '', '&', PHP_QUERY_RFC1738));
			}
			elseif($contentType === 'application/json'){
				$body = $this->streamFactory->createStream(json_encode($body));
			}

		}

		if($body instanceof StreamInterface){
			$request = $request->withBody($body);
		}

		return $this->http->sendRequest($request);
	}

}
