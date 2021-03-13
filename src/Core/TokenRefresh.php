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

interface TokenRefresh{

	/**
	 * Tries to refresh an existing access token with an associated refresh token
	 * and returns a fresh AccessToken
	 *
	 * @param \chillerlan\OAuth\Core\AccessToken|null $token
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function refreshAccessToken(AccessToken $token = null):AccessToken;

}
