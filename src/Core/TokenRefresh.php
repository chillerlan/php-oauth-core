<?php
/**
 * Interface TokenRefresh
 *
 * @filesource   TokenRefresh.php
 * @created      29.01.2018
 * @package      chillerlan\OAuth\Core
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

interface TokenRefresh{

	/**
	 * @param \chillerlan\OAuth\Core\AccessToken|null $token
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function refreshAccessToken(AccessToken $token = null):AccessToken;

}
