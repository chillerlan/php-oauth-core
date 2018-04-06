<?php
/**
 * Interface ClientCredentials
 *
 * @filesource   ClientCredentials.php
 * @created      29.01.2018
 * @package      chillerlan\OAuth\Providers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Token;

interface ClientCredentials{

	/**
	 * @param array $scopes
	 *
	 * @return \chillerlan\OAuth\Token
	 */
	public function getClientCredentialsToken(array $scopes = null):Token;

}
