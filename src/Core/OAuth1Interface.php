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
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function getRequestToken():AccessToken;

	/**
	 * @param string      $token
	 * @param string      $verifier
	 * @param string|null $tokenSecret
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function getAccessToken(string $token, string $verifier, string $tokenSecret = null):AccessToken;

	/**
	 * @param string $url
	 * @param array  $params
	 * @param string $method
	 *
	 * @return string
	 */
	public function getSignature(string $url, array $params, string $method = null):string;

}
