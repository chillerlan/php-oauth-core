<?php
/**
 * Interface OAuthInterface
 *
 * @filesource   OAuthInterface.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Providers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\HTTPResponseInterface;
use chillerlan\OAuth\Storage\TokenStorageInterface;

/**
 * @property string $serviceName
 * @property string $userRevokeURL
 *
 * from \Psr\Log\LoggerAwareTrait
 * @property \Psr\Log\LoggerInterface $logger
 * @method setLogger(\Psr\Log\LoggerInterface $logger)
 *
 * from \chillerlan\Traits\Classloader
 * @method mixed loadClass(string $class, string $type = null, ...$params)
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
	 * @return \chillerlan\HTTP\HTTPResponseInterface
	 */
	public function request(string $path, array $params = null, string $method = null, $body = null, array $headers = null):HTTPResponseInterface;

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function getStorageInterface():TokenStorageInterface;

}
