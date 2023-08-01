<?php
/**
 * Class OAuth2Provider
 *
 * @link https://tools.ietf.org/html/rfc6749
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @phan-file-suppress PhanUndeclaredMethod (CSRFToken, ClientCredentials, TokenRefresh)
 */

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use function array_merge, base64_encode, date, explode, hash_equals, implode, is_array, json_decode, random_bytes, sha1, sprintf;
use const JSON_THROW_ON_ERROR, PHP_QUERY_RFC1738;

/**
 * Implements an abstract OAuth2 provider with all methods required by the OAuth2Interface.
 * It also implements the ClientCredentials, CSRFToken and TokenRefresh interfaces in favor over traits.
 */
abstract class OAuth2Provider extends OAuthProvider implements OAuth2Interface{

	/**
	 * Specifies the authentication method:
	 *   - OAuth2Interface::AUTH_METHOD_HEADER (Bearer, OAuth, ...)
	 *   - OAuth2Interface::AUTH_METHOD_QUERY (access_token, ...)
	 */
	protected int $authMethod = self::AUTH_METHOD_HEADER;

	/**
	 * The name of the authentication header in case of OAuth2Interface::AUTH_METHOD_HEADER
	 */
	protected string $authMethodHeader = 'Bearer';

	/**
	 * The name of the authentication query parameter in case of OAuth2Interface::AUTH_METHOD_QUERY
	 */
	protected string $authMethodQuery = 'access_token';

	/**
	 * The delimiter string for scopes
	 */
	protected string $scopesDelimiter = ' ';

	/**
	 * An optional refresh token endpoint in case the provider supports TokenRefresh.
	 * If the provider supports token refresh and $refreshTokenURL is null, $accessTokenURL will be used instead.
	 *
	 * @see \chillerlan\OAuth\Core\TokenRefresh
	 */
	protected string $refreshTokenURL;

	/**
	 * An optional client credentials token endpoint in case the provider supports ClientCredentials.
	 * If the provider supports client credentials and $clientCredentialsTokenURL is null, $accessTokenURL will be used instead.
	 */
	protected ?string $clientCredentialsTokenURL = null;

	/**
	 * Default scopes to apply if none were provided via the $scopes parameter in OAuth2Provider::getAuthURL()
	 */
	protected array $defaultScopes = [];

	/**
	 * @inheritDoc
	 */
	public function getAuthURL(array $params = null, array $scopes = null):UriInterface{
		$params ??= [];
		$scopes ??= $this->defaultScopes;

		unset($params['client_secret']);

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

		return $this->uriFactory->createUri(QueryUtil::merge($this->authURL, $params));
	}

	/**
	 * Parses the response from a request to the token endpoint
	 *
	 * @link https://tools.ietf.org/html/rfc6749#section-4.1.4
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 * @throws \JsonException
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{
		// silly amazon sends compressed data...
		$data = json_decode(MessageUtil::decompress($response), true, 512, JSON_THROW_ON_ERROR);

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

		$token = $this->createAccessToken();

		$token->accessToken  = $data['access_token'];
		$token->expires      = ($data['expires_in'] ?? AccessToken::EOL_NEVER_EXPIRES);
		$token->refreshToken = ($data['refresh_token'] ?? null);

		if(isset($data['scope']) || isset($data['scopes'])){
			$scope = ($data['scope'] ?? $data['scopes'] ?? []);

			$token->scopes = (is_array($scope)) ? $scope : explode($this->scopesDelimiter, $scope);
		}

		unset($data['expires_in'], $data['refresh_token'], $data['access_token'], $data['scope'], $data['scopes']);

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
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)));

		foreach($this->authHeaders as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		$token = $this->parseTokenResponse($this->http->sendRequest($request));

		$this->storage->storeAccessToken($token, $this->serviceName);

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
			$uri = QueryUtil::merge((string)$request->getUri(), [$this->authMethodQuery => $token->accessToken]);

			return $request->withUri($this->uriFactory->createUri($uri));
		}

		throw new ProviderException('invalid auth type');
	}

	/**
	 * @implements \chillerlan\OAuth\Core\ClientCredentials
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function getClientCredentialsToken(array $scopes = null):AccessToken{

		if(!$this instanceof ClientCredentials){
			throw new ProviderException('client credentials token not supported');
		}

		$params = ['grant_type' => 'client_credentials'];

		if(!empty($scopes)){
			$params['scope'] = implode($this->scopesDelimiter, $scopes);
		}

		$request = $this->requestFactory
			->createRequest('POST', ($this->clientCredentialsTokenURL ?? $this->accessTokenURL))
			->withHeader('Authorization', 'Basic '.base64_encode($this->options->key.':'.$this->options->secret))
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withHeader('Accept-Encoding', 'identity')
			->withBody($this->streamFactory->createStream(QueryUtil::build($params, PHP_QUERY_RFC1738)))
		;

		foreach($this->authHeaders as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		$token = $this->parseTokenResponse($this->http->sendRequest($request));

		// provider didn't send a set of scopes with the token response, so add the given ones manually
		if(empty($token->scopes)){
			$token->scopes = ($scopes ?? []);
		}

		$this->storage->storeAccessToken($token, $this->serviceName);

		return $token;
	}

	/**
	 * @implements \chillerlan\OAuth\Core\TokenRefresh
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
			throw new ProviderException(
				sprintf('no refresh token available, token expired [%s]', date('Y-m-d h:i:s A', $token->expires))
			);
		}

		$body = [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
			'type'          => 'web_server',
		];

		$request = $this->requestFactory
			->createRequest('POST', ($this->refreshTokenURL ?? $this->accessTokenURL))
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withHeader('Accept-Encoding', 'identity')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)))
		;

		foreach($this->authHeaders as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		$newToken = $this->parseTokenResponse($this->http->sendRequest($request));

		if(empty($newToken->refreshToken)){
			$newToken->refreshToken = $refreshToken;
		}

		$this->storage->storeAccessToken($newToken, $this->serviceName);

		return $newToken;
	}

	/**
	 * @implements \chillerlan\OAuth\Core\CSRFToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 * @internal
	 */
	public function checkState(string $state = null):void{

		if(!$this instanceof CSRFToken){
			throw new ProviderException('CSRF protection not supported');
		}

		if(empty($state) || !$this->storage->hasCSRFState($this->serviceName)){
			throw new ProviderException('invalid state for '.$this->serviceName);
		}

		$knownState = $this->storage->getCSRFState($this->serviceName);

		if(!hash_equals($knownState, $state)){
			throw new ProviderException('invalid CSRF state: '.$this->serviceName.' '.$state);
		}

	}

	/**
	 * @implements \chillerlan\OAuth\Core\CSRFToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 * @internal
	 */
	public function setState(array $params):array{

		if(!$this instanceof CSRFToken){
			throw new ProviderException('CSRF protection not supported');
		}

		if(!isset($params['state'])){
			$params['state'] = sha1(random_bytes(256));
		}

		$this->storage->storeCSRFState($params['state'], $this->serviceName);

		return $params;
	}

}
