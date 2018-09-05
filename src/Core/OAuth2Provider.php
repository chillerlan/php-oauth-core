<?php
/**
 * Class OAuth2Provider
 *
 * @filesource   OAuth2Provider.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\HTTPClientInterface;
use chillerlan\HTTP\Psr7;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * from CSRFTokenTrait:
 * @method array setState(array $params)
 * @method \chillerlan\OAuth\Core\OAuth2Interface checkState(string $state = null)
 */
abstract class OAuth2Provider extends OAuthProvider implements OAuth2Interface{

	/**
	 * @var int
	 */
	protected $authMethod = self::HEADER_BEARER;

	/**
	 * @var array
	 */
	protected $scopes = [];

	/**
	 * @var string
	 */
	protected $scopesDelimiter = ' ';

	/**
	 * @var string
	 */
	protected $refreshTokenURL;

	/**
	 * @var string
	 */
	protected $clientCredentialsTokenURL;

	/**
	 * OAuth2Provider constructor.
	 *
	 * @param \chillerlan\HTTP\HTTPClientInterface            $http
	 * @param \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
	 * @param \chillerlan\Settings\SettingsContainerInterface $options
	 * @param array                                           $scopes
	 */
	public function __construct(HTTPClientInterface $http, OAuthStorageInterface $storage, SettingsContainerInterface $options, array $scopes = null){
		parent::__construct($http, $storage, $options);

		if($scopes !== null){
			$this->scopes = $scopes;
		}

	}

	/**
	 * @param array|null $params
	 *
	 * @return string
	 */
	public function getAuthURL(array $params = null):string{
		$params = $params ?? [];

		if(isset($params['client_secret'])){
			unset($params['client_secret']);
		}

		$params = $this->getAuthURLParams($params);

		if($this instanceof CSRFToken){
			$params = $this->setState($params);
		}

		return $this->authURL.'?'.Psr7\build_http_query($params);
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	protected function getAuthURLParams(array $params):array{
		return array_merge($params, [
			'client_id'     => $this->options->key,
			'redirect_uri'  => $this->options->callbackURL,
			'response_type' => 'code',
			'scope'         => implode($this->scopesDelimiter, $this->scopes),
			'type'          => 'web_server',
		]);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{
		$data = json_decode($response->getBody()->getContents(), true);

		if(!is_array($data)){
			throw new ProviderException('unable to parse token response');
		}

		foreach(['error_description', 'error'] as $field){

			if(isset($data[$field])){
				throw new ProviderException('error retrieving access token: "'.$data[$field].'"');
			}

		}

		if(!isset($data['access_token'])){
			throw new ProviderException('token missing');
		}

		$token = new AccessToken([
			'provider'     => $this->serviceName,
			'accessToken'  => $data['access_token'],
			'expires'      => $data['expires_in'] ?? AccessToken::EOL_NEVER_EXPIRES,
			'refreshToken' => $data['refresh_token'] ?? null,
		]);

		unset($data['expires_in'], $data['refresh_token'], $data['access_token']);

		$token->extraParams = $data;

		return $token;
	}

	/**
	 * @param string      $code
	 * @param string|null $state
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function getAccessToken(string $code, string $state = null):AccessToken{

		if($this instanceof CSRFToken){
			$this->checkState($state);
		}

		$token = $this->parseTokenResponse(
			$this->http->request(
				$this->accessTokenURL,
				'POST',
				null,
				$this->getAccessTokenBody($code),
				$this->getAccessTokenHeaders()
			)
		);

		$this->storage->storeAccessToken($this->serviceName, $token);

		return $token;
	}

	/**
	 * @param string $code
	 *
	 * @return array
	 */
	protected function getAccessTokenBody(string $code):array{
		return [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->options->callbackURL,
		];
	}

	/**
	 * @return array
	 */
	protected function getAccessTokenHeaders():array{
		return $this->authHeaders;
	}

	/**
	 * @param string $path
	 * @param array  $params
	 * @param string $method
	 * @param null   $body
	 * @param array  $headers
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function request(string $path, array $params = null, string $method = null, $body = null, array $headers = null):ResponseInterface{
		$token = $this->storage->getAccessToken($this->serviceName);

		// attempt to refresh an expired token
		if($this->options->tokenAutoRefresh && $this instanceof TokenRefresh && ($token->isExpired() || $token->expires === $token::EOL_UNKNOWN)){
			$token = $this->refreshAccessToken($token);
		}

		parse_str(parse_url($this->apiURL.$path, PHP_URL_QUERY), $query);

		$params  = array_merge($query, $params ?? []);
		$headers = $headers ?? [];

		if(array_key_exists($this->authMethod, $this::AUTH_METHODS_HEADER)){
			$headers = array_merge($headers, [
				'Authorization' => $this::AUTH_METHODS_HEADER[$this->authMethod].$token->accessToken,
			]);
		}
		elseif(array_key_exists($this->authMethod, $this::AUTH_METHODS_QUERY)){
			$params[$this::AUTH_METHODS_QUERY[$this->authMethod]] = $token->accessToken;
		}
		else{
			throw new ProviderException('invalid auth type');
		}

		return $this->http->request(
			$this->apiURL.explode('?', $path)[0],
			$method ?? 'GET',
			$params,
			$body,
			array_merge($this->apiHeaders, $headers)
		);
	}

}
