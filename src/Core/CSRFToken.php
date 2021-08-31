<?php
/**
 * Interface CSRFToken
 *
 * @created      29.01.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

/**
 * Specifies the methods required for the OAuth2 CSRF token validation ("state parameter")
 *
 * @link https://tools.ietf.org/html/rfc6749#section-10.12
 */
interface CSRFToken{

	/**
	 * Checks whether the CSRF state was set and verifies against the last known state.
	 * Throws a ProviderException if the given state is empty, unknown or doesn't match the known state.
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 * @internal
	 */
	public function checkState(string $state = null):void;

	/**
	 * Sets the CSRF state parameter in a given array of query parameters and stores that value
	 * in the local storage for later verification. Returns the updated array of parameters.
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 * @internal
	 */
	public function setState(array $params):array;

}
