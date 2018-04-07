<?php
/**
 * Trait OAuth2TokenRefreshTrait
 *
 * @filesource   OAuth2TokenRefreshTrait.php
 * @created      29.01.2018
 * @package      chillerlan\OAuth\Providers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Token;

/**
 * @property array  $authHeaders
 * @property string $accessTokenURL
 * @property string $refreshTokenURL
 * @property \chillerlan\OAuth\Storage\TokenStorageInterface $storage
 * @property \chillerlan\OAuth\OAuthOptions $options
 */
trait OAuth2TokenRefreshTrait{

	/**
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return \chillerlan\OAuth\Token
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function refreshAccessToken(Token $token = null):Token{

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

		$newToken = $this->parseTokenResponse(
			$this->httpPOST(
				$this->refreshTokenURL ?? $this->accessTokenURL,
				[],
				$this->refreshAccessTokenBody($refreshToken),
				$this->refreshAccessTokenHeaders()
			)
		);

		if(!$newToken->refreshToken){
			$newToken->refreshToken = $refreshToken;
		}

		$this->storage->storeAccessToken($this->serviceName, $newToken);

		return $newToken;
	}

	/**
	 * @param string $refreshToken
	 *
	 * @return array
	 */
	protected function refreshAccessTokenBody(string $refreshToken):array {
		return [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
			'type'          => 'web_server',
		];
	}

	/**
	 * @return array
	 */
	protected function refreshAccessTokenHeaders():array{
		return $this->authHeaders;
	}

}
