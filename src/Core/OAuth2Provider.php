<?php
/**
 * Class OAuth2Provider
 *
 * @link https://tools.ietf.org/html/rfc6749
 *
 * @filesource   OAuth2Provider.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};

use function array_merge, base64_encode, date, hash_equals, http_build_query,
	implode, is_array, json_decode, random_bytes, sha1, sprintf;
use function chillerlan\HTTP\Psr7\{decompress_content, merge_query};

use const PHP_QUERY_RFC1738;

abstract class OAuth2Provider extends OAuthProvider implements OAuth2Interface{

	/**
	 * @var int
	 */
	protected $authMethod = self::AUTH_METHOD_HEADER;

	/**
	 * @var string
	 */
	protected $authMethodHeader = 'Bearer';

	/**
	 * @var string
	 */
	protected $authMethodQuery = 'access_token';

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
	 * @inheritDoc
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

		if(!empty($scopes)){
			$params['scope'] = implode($this->scopesDelimiter, $scopes);
		}

		if($this instanceof CSRFToken){
			$params = $this->setState($params);
		}

		return $this->uriFactory->createUri(merge_query($this->authURL, $params));
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{
		$data = json_decode(decompress_content($response), true); // silly amazon...

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
	 * @inheritDoc
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
			->withHeader('Accept-Encoding', 'identity')
			->withBody($this->streamFactory->createStream(http_build_query($body, '', '&', PHP_QUERY_RFC1738)));

		foreach($this->authHeaders as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		$token = $this->parseTokenResponse($this->http->sendRequest($request));

		$this->storage->storeAccessToken($this->serviceName, $token);

		return $token;
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken $token):RequestInterface{

		if($this->authMethod === OAuth2Interface::AUTH_METHOD_HEADER){
			return $request->withHeader('Authorization', $this->authMethodHeader.' '.$token->accessToken);
		}

		if($this->authMethod === OAuth2Interface::AUTH_METHOD_QUERY){
			$uri = merge_query($request->getUri()->__toString(), [$this->authMethodQuery => $token->accessToken]);

			return $request->withUri($this->uriFactory->createUri($uri));
		}

		throw new ProviderException('invalid auth type');
	}

	/**
	 * @inheritDoc
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function getClientCredentialsToken(array $scopes = null):AccessToken{

		if(!$this instanceof ClientCredentials){
			throw new ProviderException('client credentials token not supported');
		}

		$params = ['grant_type' => 'client_credentials'];

		if($scopes !== null){
			$params['scope'] = implode($this->scopesDelimiter, $scopes);
		}

		$request = $this->requestFactory
			->createRequest('POST', $this->clientCredentialsTokenURL ?? $this->accessTokenURL)
			->withHeader('Authorization', 'Basic '.base64_encode($this->options->key.':'.$this->options->secret))
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withHeader('Accept-Encoding', 'identity')
			->withBody($this->streamFactory->createStream(http_build_query($params, '', '&', PHP_QUERY_RFC1738)))
		;

		foreach($this->authHeaders as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		$token = $this->parseTokenResponse($this->http->sendRequest($request));

		$this->storage->storeAccessToken($this->serviceName, $token);

		return $token;
	}

	/**
	 * @inheritDoc
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function refreshAccessToken(AccessToken $token = null):AccessToken{

		if(!$this instanceof TokenRefresh){
			throw new ProviderException('token refresh not supported');
		}

		if($token === null){
			$token = $this->storage->getAccessToken($this->serviceName);
		}

		$refreshToken = $token->refreshToken;

		if(empty($refreshToken)){
			throw new ProviderException(sprintf('no refresh token available, token expired [%s]', date('Y-m-d h:i:s A', $token->expires)));
		}

		$body = [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
			'type'          => 'web_server',
		];

		$request = $this->requestFactory
			->createRequest('POST', $this->refreshTokenURL ?? $this->accessTokenURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withHeader('Accept-Encoding', 'identity')
			->withBody($this->streamFactory->createStream(http_build_query($body, '', '&', PHP_QUERY_RFC1738)))
		;

		foreach($this->authHeaders as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		$newToken = $this->parseTokenResponse($this->http->sendRequest($request));

		if(empty($newToken->refreshToken)){
			$newToken->refreshToken = $refreshToken;
		}

		$this->storage->storeAccessToken($this->serviceName, $newToken);

		return $newToken;
	}

	/**
	 * @param string|null $state
	 *
	 * @return void
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function checkState(string $state = null):void{

		if(empty($state) || !$this->storage->hasCSRFState($this->serviceName)){
			throw new ProviderException('invalid state for '.$this->serviceName);
		}

		$knownState = $this->storage->getCSRFState($this->serviceName);

		if(!hash_equals($knownState, $state)){
			throw new ProviderException('invalid CSRF state: '.$this->serviceName.' '.$state);
		}

	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	protected function setState(array $params):array{

		if(!isset($params['state'])){
			$params['state'] = sha1(random_bytes(256));
		}

		$this->storage->storeCSRFState($this->serviceName, $params['state']);

		return $params;
	}

}
