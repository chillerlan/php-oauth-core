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
use Psr\Log\LoggerAwareInterface;

interface OAuthStorageInterface extends LoggerAwareInterface{

	/**
	 * Stores an AccessToken for the given $service
	 *
	 * @param string                             $service
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return bool
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function storeAccessToken(string $service, AccessToken $token):bool;

	/**
	 * Retrieves an AccessToken for the given $service
	 *
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getAccessToken(string $service):AccessToken;

	/**
	 * Checks if a token for $service exists
	 *
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken(string $service):bool;

	/**
	 * Deletes the access token for a given $service (and current user)
	 *
	 * @param string $service
	 *
	 * @return bool
	 */
	public function clearAccessToken(string $service):bool;

	/**
	 * Deletes all access tokens (for the current user)
	 *
	 * @return bool
	 */
	public function clearAllAccessTokens():bool;

	/**
	 * Stores a CSRF <state> value for the given $service
	 *
	 * @param string $service
	 * @param string $state
	 *
	 * @return bool
	 */
	public function storeCSRFState(string $service, string $state):bool;

	/**
	 * Retrieves a CSRF <state> value for the given $service
	 *
	 * @param string $service
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getCSRFState(string $service):string;

	/**
	 * Checks if a CSRF state for the given provider exists
	 *
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasCSRFState(string $service):bool;

	/**
	 * Deletes a CSRF state for the given $service (and current user)
	 *
	 * @param string $service
	 *
	 * @return bool
	 */
	public function clearCSRFState(string $service):bool;

	/**
	 * Deletes all stored CSRF states (for the current user)
	 *
	 * @return bool
	 */
	public function clearAllCSRFStates():bool;

	/**
	 * Prepares an AccessToken for storage (serialize, encrypt etc.)
	 * and returns a value that is suited for the underlying storage engine
	 *
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return mixed
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function toStorage(AccessToken $token);

	/**
	 * Retrieves token data from the underlying storage engine
	 * (decrypt, unserialize etc.) and returns an AccessToken
	 *
	 * @param mixed $data
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function fromStorage($data):AccessToken;

}
