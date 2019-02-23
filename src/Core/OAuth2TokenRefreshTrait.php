<?php
/**
 * Trait OAuth2TokenRefreshTrait
 *
 * @filesource   OAuth2TokenRefreshTrait.php
 * @created      29.01.2018
 * @package      chillerlan\OAuth\Core
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

/**
 * @implements \chillerlan\OAuth\Core\TokenRefresh
 *
 * @property array                                           $authHeaders
 * @property string                                          $accessTokenURL
 * @property string                                          $refreshTokenURL
 * @property \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
 * @property \chillerlan\OAuth\OAuthOptions                  $options
 * @property \chillerlan\HTTP\Psr18\HTTPClientInterface      $http
 * @property \Psr\Http\Message\RequestFactoryInterface       $requestFactory
 * @property \Psr\Http\Message\StreamFactoryInterface        $streamFactory
 */
trait OAuth2TokenRefreshTrait{

	/**
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken|\chillerlan\Settings\SettingsContainerInterface
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function refreshAccessToken(AccessToken $token = null):AccessToken{

		if($token === null){
			$token = $this->storage->getAccessToken($this->serviceName);
		}

		$refreshToken = $token->refreshToken;

		if(empty($refreshToken)){

			if(!$this instanceof AccessTokenForRefresh){
				throw new ProviderException(sprintf('no refresh token available, token expired [%s]', date('Y-m-d h:i:s A', $token->expires)));
			}

			$refreshToken = $token->accessToken;
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

}
