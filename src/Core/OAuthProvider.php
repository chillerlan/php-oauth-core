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
use chillerlan\HTTP\Utils\Query;
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

use function array_slice, class_exists, count, implode, in_array, is_array,
	is_scalar, is_string, json_encode, sprintf, strpos, strtolower;

use function chillerlan\HTTP\Utils\parseUrl;
use const PHP_QUERY_RFC1738;

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

		// i hate this, but i also hate adding 3 more params to the constructor
		// no i won't use a DI container for this. don't @ me
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
	 * @param string $endpointName
	 * @param array  $arguments
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \chillerlan\OAuth\MagicAPI\ApiClientException
	 */
	public function __call(string $endpointName, array $arguments):ResponseInterface{

		if(!$this->endpoints instanceof EndpointMap){
			throw new ApiClientException('MagicAPI not available'); // @codeCoverageIgnore
		}

		if(!isset($this->endpoints->{$endpointName})){
			throw new ApiClientException('endpoint not found: "'.$endpointName.'"');
		}

		// metadata for the current endpoint
		$endpointMeta  = $this->endpoints->{$endpointName};
		$path          = $this->endpoints->API_BASE.($endpointMeta['path'] ?? '');
		$method        = $endpointMeta['method'] ?? 'GET';
		$path_elements = $endpointMeta['path_elements'] ?? [];
		$query_params  = $endpointMeta['query'] ?? [];
		$headers       = $endpointMeta['headers'] ?? [];
		// the body value of the metadata is only informational
		$has_body      = isset($endpointMeta['body']) && !empty($endpointMeta['body']);

		$params = null;
		$body   = null;

		$path_element_count = count($path_elements);
		$query_param_count  = count($query_params);

		if($path_element_count > 0){
			$path = $this->parsePathElements($path, $path_elements, $path_element_count, $arguments);
		}

		if($query_param_count > 0){
			// $params is the first argument after path segments
			$params = $arguments[$path_element_count] ?? null;

			if(is_array($params)){
				$params = $this->cleanQueryParams($this->removeUnlistedParams($params, $query_params));
			}
		}

		if(in_array($method, ['POST', 'PATCH', 'PUT', 'DELETE']) && $has_body){
			// if no query params are present, $body is the first argument after any path segments
			$argPos = $query_param_count > 0 ? 1 : 0;
			$body   = $arguments[$path_element_count + $argPos] ?? null;

			if(is_array($body)){
				$body = $this->cleanBodyParams($body);
			}
		}

		$this->logger->debug('OAuthProvider::__call() -> '.$this->serviceName.'::'.$endpointName.'()', [
			'$endpoint' => $path, '$params' => $params, '$method' => $method, '$body' => $body, '$headers' => $headers,
		]);

		return $this->request($path, $params, $method, $body, $headers);
	}

	/**
	 * Checks the given path elements and returns the given path with placeholders replaced
	 *
	 * @throws \chillerlan\OAuth\MagicAPI\ApiClientException
	 */
	protected function parsePathElements(string $path, array $path_elements, int $path_element_count, array $arguments):string{
		// we don't know if all of the given arguments are path elements...
		$urlparams = array_slice($arguments, 0, $path_element_count);

		if(count($urlparams) !== $path_element_count){
			throw new APIClientException('too few URL params, required: '.implode(', ', $path_elements));
		}

		foreach($urlparams as $i => $param){
			// ...but we do know that the arguments after the path elements are usually array or null
			if(!is_scalar($param)){
				$msg = 'invalid path element value for "%s": %s';

				throw new APIClientException(sprintf($msg, $path_elements[$i], var_export($param, true)));
			}
		}

		return sprintf($path, ...$urlparams);
	}

	/**
	 * Checks an array against an allowlist and removes any parameter that is not allowed
	 */
	protected function removeUnlistedParams(array $params, array $allowed):array{
		$query = [];
		// remove any params that are not listed
		foreach($params as $key => $value){

			if(!in_array($key, $allowed, true)){
				continue;
			}

			$query[$key] = $value;
		}

		return $query;
	}

	/**
	 * Cleans an array of query parameters
	 */
	protected function cleanQueryParams(iterable $params):array{
		return Query::cleanParams($params, Query::BOOLEANS_AS_INT_STRING, true);
	}

	/**
	 * Cleans an array of body parameters
	 */
	protected function cleanBodyParams(iterable $params):array{
		return Query::cleanParams($params, Query::BOOLEANS_AS_BOOL, true);
	}

	/**
	 * Merges a set of parameters into the given querystring and returns the result querystring
	 */
	protected function mergeQuery(string $uri, array $query):string{
		return Query::merge($uri, $query);
	}

	/**
	 * Builds a query string from the given parameters
	 */
	protected function buildQuery(array $params, int $encoding = null, string $delimiter = null, string $enclosure = null):string{
		return Query::build($params, $encoding, $delimiter, $enclosure);
	}

	/**
	 * Parses the given querystring into an associative array
	 */
	protected function parseQuery(string $querystring, int $urlEncoding = null):array{
		return Query::parse($querystring, $urlEncoding);
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
			->createRequest($method ?? 'GET', $this->mergeQuery($this->getRequestTarget($path), $params ?? []));

		foreach(array_merge($this->apiHeaders, $headers ?? []) as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		if($request->hasHeader('content-type')){
			$contentType = strtolower($request->getHeaderLine('content-type'));

			if(is_array($body)){
				if($contentType === 'application/x-www-form-urlencoded'){
					$body = $this->streamFactory->createStream($this->buildQuery($body, PHP_QUERY_RFC1738));
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
	 * Determine the request target from the given URI (path segment or URL) with respect to $apiURL,
	 * anything except host and path will be ignored, scheme will always be set to "https".
	 * Throws if the given path is invalid or if the host of a given URL does not match $apiURL.
	 *
	 * @see \chillerlan\OAuth\Core\OAuthInterface::request()
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function getRequestTarget(string $uri):string{
		$parsedURL = parseUrl($uri);

		if(!isset($parsedURL['path'])){
			throw new ProviderException('invalid path');
		}

		// for some reason we were given a host name
		if(isset($parsedURL['host'])){

			// back out if it doesn't match
			if($parsedURL['host'] !== parseUrl($this->apiURL)['host']){
				throw new ProviderException('given host does not match provider host');
			}

			// we explicitly ignore any existing parameters here
			return 'https://'.$parsedURL['host'].$parsedURL['path'];
		}

		// $apiURL may already include a part of the path
		return $this->apiURL.$parsedURL['path'];
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
