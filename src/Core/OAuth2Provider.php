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

use chillerlan\HTTP\Psr7;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};

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
	 * @param array|null $params
	 * @param array|null $scopes
	 *
	 * @return \Psr\Http\Message\UriInterface
	 */
	public function getAuthURL(array $params = null, array $scopes = null):UriInterface{
		$params = $params ?? [];

		if(isset($params['client_secret'])){
			unset($params['client_secret']);
		}

		$params = array_merge($params, [
			'client_id'     => $this->options->key,
			'redirect_uri'  => $this->options->callbackURL,
			'response_type' => 'code',
			'type'          => 'web_server',
		]);

		if($scopes !== null){
			$params['scope'] = implode($this->scopesDelimiter, $scopes);
		}

		if($this instanceof CSRFToken){
			$params = $this->setState($params);
		}

		return $this->uriFactory->createUri(Psr7\merge_query($this->authURL, $params));
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{
		$data = Psr7\get_json($response, true);

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

		$body = [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->options->callbackURL,
		];

		$request = $this->requestFactory
			->createRequest('POST', $this->accessTokenURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(http_build_query($body, '', '&', PHP_QUERY_RFC1738)));

		foreach($this->authHeaders as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		$token = $this->parseTokenResponse($this->http->sendRequest($request));

		$this->storage->storeAccessToken($this->serviceName, $token);

		return $token;
	}

	/**
	 * @param \Psr\Http\Message\RequestInterface $request
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return \Psr\Http\Message\RequestInterface
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken $token):RequestInterface{

		if(array_key_exists($this->authMethod, OAuth2Interface::AUTH_METHODS_HEADER)){
			$request = $request->withHeader('Authorization', $this::AUTH_METHODS_HEADER[$this->authMethod].$token->accessToken);
		}
		elseif(array_key_exists($this->authMethod, OAuth2Interface::AUTH_METHODS_QUERY)){
			$uri = Psr7\merge_query((string)$request->getUri(), [$this::AUTH_METHODS_QUERY[$this->authMethod] => $token->accessToken]);

			$request = $request->withUri($this->uriFactory->createUri($uri));
		}
		else{
			throw new ProviderException('invalid auth type');
		}

		return $request;
	}

}
