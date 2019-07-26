<?php
/**
 * Interface OAuthStorageInterface
 *
 * @filesource   OAuthStorageInterface.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;

interface OAuthStorageInterface{

	/**
	 * @param string                             $service
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function storeAccessToken(string $service, AccessToken $token):OAuthStorageInterface;

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\OAuthException
	 */
	public function getAccessToken(string $service):AccessToken;

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken(string $service):bool;

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAccessToken(string$service):OAuthStorageInterface;

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAllAccessTokens():OAuthStorageInterface;

	/**
	 * @param string $service
	 * @param string $state
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function storeCSRFState(string $service, string $state):OAuthStorageInterface;

	/**
	 * @param string $service
	 *
	 * @return string
	 */
	public function getCSRFState(string $service):string;

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasCSRFState(string $service):bool;

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearCSRFState(string $service):OAuthStorageInterface;

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAllCSRFStates():OAuthStorageInterface;

	/**
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return string
	 */
	public function toStorage(AccessToken $token):string;

	/**
	 * @param string $data
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function fromStorage(string $data):AccessToken;

}
