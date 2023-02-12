<?php
/**
 * Interface TokenInvalidate
 *
 * @created      12.02.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

/**
 * Indicates whether the service is capable of invalidating access tokens
 */
interface TokenInvalidate{

	/**
	 * Allows to invalidate an access token
	 *
	 * If a token is given via $token, that token should be invalidated,
	 * otherwise the current user token from the internal storage should be used.
	 * Returns true if the operation was successful, false otherwise.
	 * May throw a ProviderException if an error occured.
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function invalidateAccessToken(AccessToken $token = null):bool;

}
