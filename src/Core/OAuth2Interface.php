<?php
/**
 * Interface OAuth2Interface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use Psr\Http\Message\UriInterface;

/**
 * Specifies the basic methods for an OAuth2 provider.
 */
interface OAuth2Interface extends OAuthInterface{

	public const AUTH_METHOD_HEADER = 1;
	public const AUTH_METHOD_QUERY  = 2;

	/**
	 * Obtains an OAuth2 access token with the given $code, verifies the $state
	 * if the provider implements the CSRFToken interface, and returns an AccessToken object
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.3
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken;

	/**
	 * Prepares the URL with optional $params and $scopes which redirects to the provider's authorization prompt
	 * and returns a PSR-7 UriInterface with all necessary parameters set
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
	 *
	 * @param array|null    $params
	 * @param string[]|null $scopes
	 */
	public function getAuthURL(array|null $params = null, array|null $scopes = null):UriInterface;

}
