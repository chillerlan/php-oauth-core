<?php
/**
 * Interface OAuth2Interface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use Psr\Http\Message\UriInterface;

interface OAuth2Interface extends OAuthInterface{

	const AUTH_METHOD_HEADER = 1;
	const AUTH_METHOD_QUERY  = 2;

	/**
	 * Obtains an OAuth2 access token with the given $code, verifies the $state
	 * if the provider implements the CSRFToken interface, and returns an AccessToken object
	 *
	 * @param string      $code
	 * @param string|null $state
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function getAccessToken(string $code, string $state = null):AccessToken;

	/**
	 * Prepares the URL with optional $params and $scopes which redirects to the provider's authorization prompt
	 * and returns a PSR-7 UriInterface with all necessary parameters set
	 *
	 * @param array|null $params
	 * @param array|null $scopes
	 *
	 * @return \Psr\Http\Message\UriInterface
	 */
	public function getAuthURL(array $params = null, array $scopes = null):UriInterface;

}
