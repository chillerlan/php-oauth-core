<?php
/**
 * Interface OAuth2Interface
 *
 * @filesource   OAuth2Interface.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use Psr\Http\Message\UriInterface;

interface OAuth2Interface extends OAuthInterface{

	const HEADER_OAUTH              = 0;
	const HEADER_BEARER             = 1;
	const QUERY_ACCESS_TOKEN        = 2;
	const QUERY_OAUTH2_ACCESS_TOKEN = 3;
	const QUERY_APIKEY              = 4;
	const QUERY_AUTH                = 5;
	const QUERY_OAUTH_TOKEN         = 6;

	const AUTH_METHODS_HEADER = [
		self::HEADER_OAUTH  => 'OAuth ',
		self::HEADER_BEARER => 'Bearer ',
	];

	const AUTH_METHODS_QUERY = [
		self::QUERY_ACCESS_TOKEN        => 'access_token',
		self::QUERY_OAUTH2_ACCESS_TOKEN => 'oauth2_access_token',
		self::QUERY_APIKEY              => 'apikey',
		self::QUERY_AUTH                => 'auth',
		self::QUERY_OAUTH_TOKEN         => 'oauth_token',
	];

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
