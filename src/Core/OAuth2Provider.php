<?php
/**
 * Class OAuth2Provider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Providers\ProviderException;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use function array_merge, base64_encode, date, explode, hash_equals, implode, is_array, json_decode, random_bytes, sha1, sprintf;
use const JSON_THROW_ON_ERROR, PHP_QUERY_RFC1738;

/**
 * Implements an abstract OAuth2 provider with all methods required by the OAuth2Interface.
 * It also implements the ClientCredentials, CSRFToken and TokenRefresh interfaces in favor over traits.

 *  @see https://datatracker.ietf.org/doc/html/rfc6749
 */
abstract class OAuth2Provider extends OAuthProvider implements OAuth2Interface{

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
	protected string|null $clientCredentialsTokenURL = null;

	/**
	 * @inheritDoc
	 * @param string[]|null $scopes
	 */
	public function getAuthURL(array|null $params = null, array|null $scopes = null):UriInterface{
		$params ??= [];
		$scopes ??= $this::DEFAULT_SCOPES;

		unset($params['client_secret']);

		$params = array_merge($params, [
			'client_id'     => $this->options->key,
			'redirect_uri'  => $this->options->callbackURL,
			'response_type' => 'code',
			'type'          => 'web_server',
		]);

		if(!empty($scopes)){
			$params['scope'] = implode($this::SCOPE_DELIMITER, $scopes);
		}

		if($this instanceof CSRFToken){
			$params = $this->setState($params);
		}

		return $this->uriFactory->createUri(QueryUtil::merge($this->authURL, $params));
	}

	/**
	 * Parses the response from a request to the token endpoint
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.4
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
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
				throw new ProviderException(sprintf('error retrieving access token: "%s"', $data[$field]));
			}
		}

		if(!isset($data['access_token'])){
			throw new ProviderException('token missing');
		}

		$scopes = ($data['scope'] ?? $data['scopes'] ?? []);

		if(!is_array($scopes)){
			$scopes = explode($this::SCOPE_DELIMITER, $scopes);
		}

		$token               = $this->createAccessToken();
		$token->accessToken  = $data['access_token'];
		$token->expires      = ($data['expires_in'] ?? AccessToken::EOL_NEVER_EXPIRES);
		$token->refreshToken = ($data['refresh_token'] ?? null);
		$token->scopes       = $scopes;

		unset($data['access_token'], $data['refresh_token'], $data['expires_in'], $data['scope'], $data['scopes']);

		$token->extraParams  = $data;

		return $token;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken{

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

		foreach($this::HEADERS_AUTH as $header => $value){
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

		if($this::AUTH_METHOD === OAuth2Interface::AUTH_METHOD_HEADER){
			return $request->withHeader('Authorization', $this::AUTH_PREFIX_HEADER.' '.$token->accessToken);
		}

		if($this::AUTH_METHOD === OAuth2Interface::AUTH_METHOD_QUERY){
			$uri = QueryUtil::merge((string)$request->getUri(), [$this::AUTH_PREFIX_QUERY => $token->accessToken]);

			return $request->withUri($this->uriFactory->createUri($uri));
		}

		throw new ProviderException('invalid auth AUTH_METHOD');
	}

	/**
	 * @param string[]|null $scopes
	 * @implements \chillerlan\OAuth\Core\ClientCredentials
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getClientCredentialsToken(array|null $scopes = null):AccessToken{

		if(!$this instanceof ClientCredentials){
			throw new ProviderException('client credentials token not supported');
		}

		$params = ['grant_type' => 'client_credentials'];

		if(!empty($scopes)){
			$params['scope'] = implode($this::SCOPE_DELIMITER, $scopes);
		}

		$request = $this->requestFactory
			->createRequest('POST', ($this->clientCredentialsTokenURL ?? $this->accessTokenURL))
			->withHeader('Authorization', 'Basic '.base64_encode($this->options->key.':'.$this->options->secret))
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withHeader('Accept-Encoding', 'identity')
			->withBody($this->streamFactory->createStream(QueryUtil::build($params, PHP_QUERY_RFC1738)))
		;

		foreach($this::HEADERS_AUTH as $header => $value){
			$request = $request->withHeader($header, $value);
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
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function refreshAccessToken(AccessToken|null $token = null):AccessToken{

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

		foreach($this::HEADERS_AUTH as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		$newToken = $this->parseTokenResponse($this->http->sendRequest($request));

		if(empty($newToken->refreshToken)){
			$newToken->refreshToken = $refreshToken;
		}

		$this->storage->storeAccessToken($newToken, $this->serviceName);

		return $newToken;
	}

	/**
	 * @implements \chillerlan\OAuth\Core\CSRFToken::checkState()
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 * @internal
	 */
	public function checkState(string|null $state = null):void{

		if(!$this instanceof CSRFToken){
			throw new ProviderException('CSRF protection not supported');
		}

		if(empty($state) || !$this->storage->hasCSRFState($this->serviceName)){
			throw new ProviderException(sprintf('invalid state for "%s"', $this->serviceName));
		}

		$knownState = $this->storage->getCSRFState($this->serviceName);

		if(!hash_equals($knownState, $state)){
			throw new ProviderException(sprintf('invalid CSRF state for provider "%s": %s', $this->serviceName, $state));
		}

	}

	/**
	 * @implements \chillerlan\OAuth\Core\CSRFToken::setState()
	 * @throws \chillerlan\OAuth\Providers\ProviderException
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
