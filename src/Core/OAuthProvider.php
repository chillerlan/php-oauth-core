<?php
/**
 * Class OAuthProvider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @phan-file-suppress PhanUndeclaredProperty (MagicAPI\ApiClientInterface)
 */

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Psr17\{RequestFactory, StreamFactory, UriFactory};
use chillerlan\OAuth\MagicAPI\{ApiClientException, EndpointMap, EndpointMapInterface};
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{
	RequestFactoryInterface, RequestInterface, ResponseInterface,
	StreamFactoryInterface, StreamInterface, UriFactoryInterface
};
use Psr\Log\{LoggerAwareTrait, LoggerInterface, NullLogger};
use ReflectionClass;

use function array_slice, class_exists, count, http_build_query, implode,
	in_array, is_array, is_string, json_encode, sprintf, strpos, strtolower;
use function chillerlan\HTTP\Psr7\{clean_query_params, merge_query};

use const PHP_QUERY_RFC1738;
use const chillerlan\HTTP\Psr7\{BOOLEANS_AS_INT_STRING, BOOLEANS_AS_BOOL};

/**
 * Implements an abstract OAuth provider with all methods required by the OAuthInterface.
 * It also implements a magic getter that allows to access the properties listed below.
 *
 * @property string|null                                     $apiDocs
 * @property string                                          $apiURL
 * @property string|null                                     $applicationURL
 * @property \chillerlan\OAuth\MagicAPI\EndpointMapInterface $endpoints
 * @property string                                          $serviceName
 * @property string|null                                     $userRevokeURL
 */
abstract class OAuthProvider implements OAuthInterface{
	use LoggerAwareTrait;

	protected const ALLOWED_PROPERTIES = [
		'apiDocs', 'apiURL', 'applicationURL', 'endpoints', 'serviceName', 'userRevokeURL'
	];

	/**
	 * the http client instance
	 */
	protected ClientInterface $http;

	/**
	 * the token storage instance
	 */
	protected OAuthStorageInterface $storage;

	/**
	 * the options instance
	 *
	 * @var \chillerlan\OAuth\OAuthOptions|\chillerlan\Settings\SettingsContainerInterface
	 */
	protected SettingsContainerInterface $options;

	/**
	 * the API endpoints (optional) (magic)
	 */
	protected ?EndpointMapInterface $endpoints = null;

	/**
	 * an optional PSR-17 request factory
	 */
	protected RequestFactoryInterface $requestFactory;

	/**
	 * an optional PSR-17 stream factory
	 */
	protected StreamFactoryInterface  $streamFactory;

	/**
	 * an optional PSR-17 URI factory
	 */
	protected UriFactoryInterface $uriFactory;

	/**
	 * the name of the provider (class) (magic)
	 */
	protected ?string $serviceName = null;

	/**
	 * the authentication URL
	 */
	protected string $authURL;

	/**
	 * an optional link to the provider's API docs (magic)
	 */
	protected ?string $apiDocs = null;

	/**
	 * the API base URL (magic)
	 */
	protected ?string $apiURL = null;

	/**
	 * an optional URL to the provider's credential registration/application page (magic)
	 */
	protected ?string $applicationURL = null;

	/**
	 * an optional link to the page where a user can revoke access tokens (magic)
	 */
	protected ?string $userRevokeURL = null;

	/**
	 * an optional URL for application side token revocation
	 */
	protected ?string $revokeURL = null;

	/**
	 * the provider's access token exchange URL
	 */
	protected string $accessTokenURL;

	/**
	 * an optional EndpointMapInterface FQCN
	 */
	protected ?string $endpointMap = null;

	/**
	 * additional headers to use during authentication
	 */
	protected array $authHeaders = [];

	/**
	 * additional headers to use during API access
	 */
	protected array $apiHeaders = [];

	/**
	 * OAuthProvider constructor.
	 *
	 * @param \Psr\Http\Client\ClientInterface                $http
	 * @param \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
	 * @param \chillerlan\Settings\SettingsContainerInterface $options
	 * @param \Psr\Log\LoggerInterface|null                   $logger
	 *
	 * @throws \chillerlan\OAuth\MagicAPI\ApiClientException
	 */
	public function __construct(
		ClientInterface $http,
		OAuthStorageInterface $storage,
		SettingsContainerInterface $options,
		LoggerInterface $logger = null
	){
		$this->http    = $http;
		$this->storage = $storage;
		$this->options = $options;
		$this->logger  = $logger ?? new NullLogger;

		$this->requestFactory = new RequestFactory;
		$this->streamFactory  = new StreamFactory;
		$this->uriFactory     = new UriFactory;

		$this->serviceName = (new ReflectionClass($this))->getShortName();

		if(!empty($this->endpointMap) && class_exists($this->endpointMap)){
			$this->endpoints = new $this->endpointMap;

			if(!$this->endpoints instanceof EndpointMapInterface){
				throw new ApiClientException('invalid endpoint map'); // @codeCoverageIgnore
			}

		}

	}

	/**
	 * Magic getter for the properties specified in self::ALLOWED_PROPERTIES
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get(string $name){

		if(in_array($name, $this::ALLOWED_PROPERTIES, true)){
			return $this->{$name};
		}

		return null;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setStorage(OAuthStorageInterface $storage):OAuthInterface{
		$this->storage = $storage;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setRequestFactory(RequestFactoryInterface $requestFactory):OAuthInterface{
		$this->requestFactory = $requestFactory;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setStreamFactory(StreamFactoryInterface $streamFactory):OAuthInterface{
		$this->streamFactory = $streamFactory;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setUriFactory(UriFactoryInterface $uriFactory):OAuthInterface{
		$this->uriFactory = $uriFactory;

		return $this;
	}

	/**
	 * Magic API endpoint access. ugly, isn't it?
	 *
	 * @todo WIP
	 *
	 * @param string $endpointName
	 * @param array  $arguments
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \chillerlan\OAuth\MagicAPI\ApiClientException
	 * @codeCoverageIgnore
	 */
	public function __call(string $endpointName, array $arguments):ResponseInterface{

		if(!$this->endpoints instanceof EndpointMap){
			throw new ApiClientException('MagicAPI not available');
		}

		if(!isset($this->endpoints->{$endpointName})){
			throw new ApiClientException('endpoint not found: "'.$endpointName.'"');
		}

		$m = $this->endpoints->{$endpointName};

		$endpoint      = $this->endpoints->API_BASE.$m['path'];
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

			$body = $this->cleanBodyParams($body);
		}

		$params = $this->cleanQueryParams($params);

		$this->logger->debug('OAuthProvider::__call() -> '.$this->serviceName.'::'.$endpointName.'()', [
			'$endpoint' => $endpoint, '$params' => $params, '$method' => $method, '$body' => $body, '$headers' => $headers,
		]);

		return $this->request($endpoint, $params, $method, $body, $headers);
	}

	/**
	 * Cleans an array of query parameters
	 *
	 * @param array $params
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	protected function cleanQueryParams(array $params):array{
		return clean_query_params($params, BOOLEANS_AS_INT_STRING, true);
	}

	/**
	 * Cleans an array of body parameters
	 *
	 * @param array $params
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	protected function cleanBodyParams(array $params):array{
		return clean_query_params($params, BOOLEANS_AS_BOOL, true);
	}

	/**
	 * @inheritDoc
	 */
	public function request(
		string $path,
		array $params = null,
		string $method = null,
		$body = null,
		array $headers = null
	):ResponseInterface{

		$request = $this->requestFactory
			->createRequest($method ?? 'GET', merge_query($this->apiURL.$path, $params ?? []));

		foreach(array_merge($this->apiHeaders, $headers ?? []) as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		if($request->hasHeader('content-type')){
			$contentType = strtolower($request->getHeaderLine('content-type'));

			if(is_array($body)){
				if($contentType === 'application/x-www-form-urlencoded'){
					$body = $this->streamFactory->createStream(http_build_query($body, '', '&', PHP_QUERY_RFC1738));
				}
				elseif($contentType === 'application/json' || $contentType === 'application/vnd.api+json'){
					$body = $this->streamFactory->createStream(json_encode($body));
				}
			}
			elseif(is_string($body)){
				// we don't check if the given string matches the content type - this is the implementor's responsibility
				$body = $this->streamFactory->createStream($body);
			}
		}

		if($body instanceof StreamInterface){
			$request = $request
				->withBody($body)
				->withHeader('Content-length', (string)$body->getSize())
			;
		}

		return $this->sendRequest($request);
	}

	/**
	 * @inheritDoc
	 */
	public function sendRequest(RequestInterface $request):ResponseInterface{

		// get authorization only if we request the provider API
		if(strpos((string)$request->getUri(), $this->apiURL) === 0){
			$token = $this->storage->getAccessToken($this->serviceName);

			// attempt to refresh an expired token
			if(
				$this instanceof TokenRefresh
				&& $this->options->tokenAutoRefresh
				&& ($token->isExpired() || $token->expires === $token::EOL_UNKNOWN)
			){
				$token = $this->refreshAccessToken($token);
			}

			$request = $this->getRequestAuthorization($request, $token);
		}

		return $this->http->sendRequest($request);
	}

}
