<?php
/**
 * Class OAuthProvider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Psr17\{RequestFactory, StreamFactory, UriFactory};
use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{
	RequestFactoryInterface, RequestInterface, ResponseInterface,
	StreamFactoryInterface, StreamInterface, UriFactoryInterface
};
use Psr\Log\{LoggerAwareTrait, LoggerInterface, NullLogger};
use ReflectionClass;
use function array_merge;
use function in_array;
use function is_array;
use function is_string;
use function json_encode;
use function sprintf;
use function str_starts_with;
use function strtolower;
use const PHP_QUERY_RFC1738;

/**
 * Implements an abstract OAuth provider with all methods required by the OAuthInterface.
 * It also implements a magic getter that allows to access the properties listed below.
 *
 * @property string|null $apiDocs
 * @property string      $apiURL
 * @property string|null $applicationURL
 * @property string      $serviceName
 * @property string|null $userRevokeURL
 */
abstract class OAuthProvider implements OAuthInterface{
	use LoggerAwareTrait;

	protected const ALLOWED_PROPERTIES = [
		'apiDocs', 'apiURL', 'applicationURL', 'serviceName', 'userRevokeURL'
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
	 */
	protected OAuthOptions|SettingsContainerInterface $options;

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
	 * additional headers to use during authentication
	 */
	protected array $authHeaders = [];

	/**
	 * additional headers to use during API access
	 */
	protected array $apiHeaders = [];

	/**
	 * OAuthProvider constructor.
	 */
	public function __construct(
		ClientInterface $http,
		OAuthStorageInterface $storage,
		OAuthOptions|SettingsContainerInterface $options,
		LoggerInterface $logger = null
	){
		$this->http    = $http;
		$this->storage = $storage;
		$this->options = $options;
		$this->logger  = $logger ?? new NullLogger;

		// i hate this, but i also hate adding 3 more params to the constructor
		// no, i won't use a DI container for this. don't @ me
		$this->requestFactory = new RequestFactory;
		$this->streamFactory  = new StreamFactory;
		$this->uriFactory     = new UriFactory;
		$this->serviceName    = (new ReflectionClass($this))->getShortName();
	}

	/**
	 * Magic getter for the properties specified in self::ALLOWED_PROPERTIES
	 *
	 * @return mixed|null
	 */
	public function __get(string $name):mixed{

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
	public function getStorage():OAuthStorageInterface{
		return $this->storage;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function storeAccessToken(AccessToken $token):OAuthInterface{
		$this->storage->storeAccessToken($this->serviceName, $token);

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
	 * Creates an access token with the provider set to $this->serviceName
	 */
	protected function createAccessToken():AccessToken{
		return new AccessToken(['provider' => $this->serviceName]);
	}

	/**
	 * @inheritDoc
	 */
	public function request(
		string $path,
		array $params = null,
		string $method = null,
		StreamInterface|array|string $body = null,
		array $headers = null
	):ResponseInterface{

		$request = $this->requestFactory
			->createRequest($method ?? 'GET', QueryUtil::merge($this->getRequestTarget($path), $params ?? []));

		foreach(array_merge($this->apiHeaders, $headers ?? []) as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		if($request->hasHeader('content-type')){
			$contentType = strtolower($request->getHeaderLine('content-type'));

			if(is_array($body)){
				if($contentType === 'application/x-www-form-urlencoded'){
					$body = $this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738));
				}
				elseif(in_array($contentType, ['application/json', 'application/vnd.api+json'])){
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
		$parsedURL = QueryUtil::parseUrl($uri);

		if(!isset($parsedURL['path'])){
			throw new ProviderException('invalid path');
		}

		// for some reason we were given a host name
		if(isset($parsedURL['host'])){
			$api  = QueryUtil::parseUrl($this->apiURL);
			$host = $api['host'] ?? null;

			// back out if it doesn't match
			if($parsedURL['host'] !== $host){
				throw new ProviderException(sprintf('given host (%s) does not match provider (%s)', $parsedURL['host'] , $host));
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

}
