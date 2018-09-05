<?php
/**
 * Interface OAuthInterface
 *
 * @filesource   OAuthInterface.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\OAuth\Storage\OAuthStorageInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @property string $accessTokenURL
 * @property string $authURL
 * @property string $revokeURL
 * @property string $serviceName
 * @property string $userRevokeURL
 */
interface OAuthInterface{

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public function getAuthURL(array $params = null):string;

	/**
	 * @param string $path
	 * @param array  $params
	 * @param string $method
	 * @param null   $body
	 * @param array  $headers
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function request(string $path, array $params = null, string $method = null, $body = null, array $headers = null):ResponseInterface;

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function getStorageInterface():OAuthStorageInterface;

}
