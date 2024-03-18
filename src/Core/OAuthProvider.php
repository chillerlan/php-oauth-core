<?php
/**
 * Class OAuthProvider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{
	RequestFactoryInterface, RequestInterface, ResponseInterface,
	StreamFactoryInterface, StreamInterface, UriFactoryInterface
};
use Psr\Log\{LoggerInterface, NullLogger};
use ReflectionClass;
use function array_merge, in_array, is_array, is_string, json_encode, ltrim, rtrim, sprintf, str_starts_with, strtolower;
use const PHP_QUERY_RFC1738;

/**
 * Implements an abstract OAuth provider with all methods required by the OAuthInterface.
 * It also implements a magic getter that allows to access the properties listed below.
 */
abstract class OAuthProvider implements OAuthInterface{

	/**
	 * the authentication URL
	 */
	protected string $authURL;

	/**
	 * an optional URL for application side token revocation
	 *
	 * @see \chillerlan\OAuth\Core\TokenInvalidate
	 */
	protected string $revokeURL;

	/**
	 * the provider's access token exchange URL
	 */
	protected string $accessTokenURL;

	/*
	 * magic properties (public readonly would be cool if the implementation wasn't fucking stupid)
	 */

	/** @var string[] */
	protected const MAGIC_PROPERTIES = [
		'apiDocs', 'apiURL', 'applicationURL', 'serviceName', 'userRevokeURL',
	];

	/**
	 * the name of the provider/class (magic)
	 */
	protected string $serviceName;

	/**
	 * the API base URL (magic)
	 */
	protected string $apiURL;

	/**
	 * an optional link to the provider's API docs (magic)
	 */
	protected string|null $apiDocs = null;

	/**
	 * an optional URL to the provider's credential registration/application page (magic)
	 */
	protected string|null $applicationURL = null;

	/**
	 * an optional link to the page where a user can revoke access tokens (magic)
	 */
	protected string|null $userRevokeURL = null;

	/**
	 * OAuthProvider constructor.
	 */
	public function __construct(
		protected OAuthOptions|SettingsContainerInterface $options,
		protected ClientInterface                         $http,
		protected RequestFactoryInterface                 $requestFactory,
		protected StreamFactoryInterface                  $streamFactory,
		protected UriFactoryInterface                     $uriFactory,
		protected OAuthStorageInterface                   $storage = new MemoryStorage,
		protected LoggerInterface                         $logger = new NullLogger,
	){
		$this->serviceName = (new ReflectionClass($this))->getShortName();

		$this->construct();
	}

	/**
	 * A replacement constructor that you can call in extended classes,
	 * so that you don't have to implement the monstrous original `__construct()`
	 */
	protected function construct():void{
		// noop
	}

	/**
	 * Magic getter for the properties specified in self::ALLOWED_PROPERTIES
	 */
	public function __get(string $name):string|null{

		if(in_array($name, $this::MAGIC_PROPERTIES, true)){
			return $this->{$name};
		}

		return null;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setStorage(OAuthStorageInterface $storage):static{
		$this->storage = $storage;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function getStorage():OAuthStorageInterface{
		return $this->storage;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setLogger(LoggerInterface $logger):static{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setRequestFactory(RequestFactoryInterface $requestFactory):static{
		$this->requestFactory = $requestFactory;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setStreamFactory(StreamFactoryInterface $streamFactory):static{
		$this->streamFactory = $streamFactory;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setUriFactory(UriFactoryInterface $uriFactory):static{
		$this->uriFactory = $uriFactory;

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function storeAccessToken(AccessToken $token):static{
		$this->storage->storeAccessToken($token, $this->serviceName);

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function getAccessTokenFromStorage():AccessToken{
		return $this->storage->getAccessToken($this->serviceName);
	}

	/**
	 * Creates an access token with the provider set to $this->serviceName
	 *
	 * @codeCoverageIgnore
	 */
	protected function createAccessToken():AccessToken{
		return new AccessToken(['provider' => $this->serviceName]);
	}

	/**
	 * Prepare request headers
	 */
	protected function getRequestHeaders(array|null $headers = null):array{
		/** @noinspection PhpParamsInspection sup PHPStorm?? */
		return array_merge($this::HEADERS_API, ($headers ?? []));
	}

	/**
	 * Prepares the request URL
	 */
	protected function getRequestURL(string $path, array|null $params = null):string{
		return QueryUtil::merge($this->getRequestTarget($path), $this->cleanQueryParams(($params ?? [])));
	}

	/**
	 * Cleans an array of query parameters
	 */
	protected function cleanQueryParams(iterable $params):array{
		return QueryUtil::cleanParams($params, QueryUtil::BOOLEANS_AS_INT_STRING, true);
	}

	/**
	 * Cleans an array of body parameters
	 */
	protected function cleanBodyParams(iterable $params):array{
		return QueryUtil::cleanParams($params, QueryUtil::BOOLEANS_AS_BOOL, true);
	}

	/**
	 * @inheritDoc
	 */
	public function request(
		string                            $path,
		array|null                        $params = null,
		string|null                       $method = null,
		StreamInterface|array|string|null $body = null,
		array|null                        $headers = null,
		string|null                       $protocolVersion = null,
	):ResponseInterface{
		$request = $this->requestFactory->createRequest(($method ?? 'GET'), $this->getRequestURL($path, $params));

		foreach($this->getRequestHeaders($headers) as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		if($body !== null){
			$body    = $this->getRequestBody($body, $request);
			$request = $request
				->withBody($body)
				->withHeader('Content-length', (string)$body->getSize())
			;
		}

		if($protocolVersion !== null){
			$request = $request->withProtocolVersion($protocolVersion);
		}

		return $this->sendRequest($request);
	}

	/**
	 * Prepares the request body
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function getRequestBody(StreamInterface|array|string $body, RequestInterface $request):StreamInterface{

		if($body instanceof StreamInterface){
			return $body;
		}

		if(is_string($body)){
			// we don't check if the given string matches the content type - this is the implementor's responsibility
			return $this->streamFactory->createStream($body);
		}

		if(is_array($body)){
			$body        = $this->cleanBodyParams($body);
			$contentType = strtolower($request->getHeaderLine('content-type'));

			if($contentType === 'application/x-www-form-urlencoded'){
				return $this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738));
			}

			if(in_array($contentType, ['application/json', 'application/vnd.api+json'])){
				return $this->streamFactory->createStream(json_encode($body));
			}

		}

		throw new ProviderException('invalid body/content-type');  // @codeCoverageIgnore
	}

	/**
	 * Determine the request target from the given URI (path segment or URL) with respect to $apiURL,
	 * anything except host and path will be ignored, scheme will always be set to "https".
	 * Throws if the given path is invalid or if the host of a given URL does not match $apiURL.
	 *
	 * @see \chillerlan\OAuth\Core\OAuthInterface::request()
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function getRequestTarget(string $uri):string{
		$parsedURL  = $this->uriFactory->createUri($uri);
		$parsedHost = $parsedURL->getHost();
		$api        = $this->uriFactory->createUri($this->apiURL);

		// for some reason we were given a host name
		if($parsedHost !== ''){
			$apiHost = $api->getHost();

			// back out if it doesn't match
			if($parsedHost !== $apiHost){
				throw new ProviderException(sprintf('given host (%s) does not match provider (%s)', $parsedHost , $apiHost));
			}

			// we explicitly ignore any existing parameters here
			return (string)$parsedURL->withQuery('')->withFragment('');
		}

		$parsedPath = $parsedURL->getPath();
		$apiURL     = rtrim((string)$api, '/');

		if($parsedPath === ''){
			return $apiURL;
		}

		return sprintf('%s/%s', $apiURL, ltrim($parsedPath, '/'));
	}

	/**
	 * @inheritDoc
	 */
	public function sendRequest(RequestInterface $request):ResponseInterface{

		// get authorization only if we request the provider API
		if(str_starts_with((string)$request->getUri(), $this->apiURL)){
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

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():ResponseInterface{
		throw new ProviderException('not implemented');
	}

	/**
	 * @implements \chillerlan\OAuth\Core\TokenInvalidate
	 * @codeCoverageIgnore
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function invalidateAccessToken(AccessToken|null $token = null):bool{
		throw new ProviderException('not implemented');
	}

}
