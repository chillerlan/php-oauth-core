<?php
/**
 * Interface TokenRefresh
 *
 * @filesource   TokenRefresh.php
 * @created      29.01.2018
 * @package      chillerlan\OAuth\Providers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Token;

interface TokenRefresh{

	/**
	 * @param \chillerlan\OAuth\Token|null $token
	 *
	 * @return \chillerlan\OAuth\Token
	 */
	public function refreshAccessToken(Token $token = null):Token;

}
