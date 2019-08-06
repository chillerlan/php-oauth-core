<?php
/**
 * Interface OAuth1Interface
 *
 * @filesource   OAuth1Interface.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

interface OAuth1Interface extends OAuthInterface{

	/**
	 * Obtains an OAuth1 request token and returns an AccessToken
	 * object for use in the authentication request
	 *
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::getAuthURL()
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function getRequestToken():AccessToken;

	/**
	 * Obtains an OAuth1 access token with the given $token and $verifier
	 * and returns an AccessToken object
	 *
	 * @param string      $token
	 * @param string      $verifier
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	public function getAccessToken(string $token, string $verifier):AccessToken;

}
