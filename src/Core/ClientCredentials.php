<?php
/**
 * Interface ClientCredentials
 *
 * @filesource   ClientCredentials.php
 * @created      29.01.2018
 * @package      chillerlan\OAuth\Core
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

interface ClientCredentials{

	/**
	 * @param array $scopes
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken|\chillerlan\Settings\SettingsContainerInterface
	 */
	public function getClientCredentialsToken(array $scopes = null):AccessToken;

}
