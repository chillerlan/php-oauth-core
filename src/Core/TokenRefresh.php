<?php
/**
 * Interface TokenRefresh
 *
 * @created      29.01.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

/**
 * Specifies whether the provider is capable of the OAuth2 token refresh.
 *
 * @link https://tools.ietf.org/html/rfc6749#section-10.4
 */
interface TokenRefresh{

	/**
	 * Tries to refresh an existing AccessToken with an associated refresh token and returns a fresh AccessToken.
	 *
	 * @param \chillerlan\OAuth\Core\AccessToken|null $token
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function refreshAccessToken(AccessToken $token = null):AccessToken;

}
