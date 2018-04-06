<?php
/**
 * Trait OAuth2ClientCredentialsTrait
 *
 * @filesource   OAuth2ClientCredentialsTrait.php
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
 * @property string $scopesDelimiter
 * @property string $clientCredentialsTokenURL
 * @property string $accessTokenURL
 * @property \chillerlan\OAuth\Storage\TokenStorageInterface $storage
 */
trait OAuth2ClientCredentialsTrait{

	/**
	 * @param array $scopes
	 *
	 * @return \chillerlan\OAuth\Token
	 */
	public function getClientCredentialsToken(array $scopes = null):Token {
		$token = $this->parseTokenResponse(
			$this->httpPOST(
				$this->clientCredentialsTokenURL ?? $this->accessTokenURL,
				[],
				$this->getClientCredentialsTokenBody($scopes ?? []),
				$this->getClientCredentialsTokenHeaders()
			)
		);

		$this->storage->storeAccessToken($this->serviceName, $token);

		return $token;
	}

	/**
	 * @param array $scopes
	 *
	 * @return array
	 */
	protected function getClientCredentialsTokenBody(array $scopes):array {
		return [
			'grant_type' => 'client_credentials',
			'scope'      => implode($this->scopesDelimiter, $scopes),
		];
	}

	/**
	 * @return array
	 */
	protected function getClientCredentialsTokenHeaders():array {
		return array_merge($this->authHeaders, [
			'Authorization' => 'Basic '.base64_encode($this->options->key.':'.$this->options->secret),
		]);
	}

}
