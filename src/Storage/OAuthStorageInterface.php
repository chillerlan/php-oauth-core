<?php
/**
 * Interface OAuthStorageInterface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;
use Psr\Log\LoggerAwareInterface;

/**
 * Specifies the methods required for an OAuth storage adapter
 */
interface OAuthStorageInterface extends LoggerAwareInterface{

	/**
	 * Sets the current service provider name
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function setServiceName(string $service):OAuthStorageInterface;

	/**
	 * Gets the current service provider name
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getServiceName(string $service = null):string;

	/**
	 * Stores an AccessToken for the given $service
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function storeAccessToken(AccessToken $token, string $service = null):OAuthStorageInterface;

	/**
	 * Retrieves an AccessToken for the given $service
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getAccessToken(string $service = null):AccessToken;

	/**
	 * Checks if a token for $service exists
	 */
	public function hasAccessToken(string $service = null):bool;

	/**
	 * Deletes the access token for a given $service (and current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAccessToken(string $service = null):OAuthStorageInterface;

	/**
	 * Deletes all access tokens (for the current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAllAccessTokens():OAuthStorageInterface;

	/**
	 * Stores a CSRF <state> value for the given $service
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function storeCSRFState(string $state, string $service = null):OAuthStorageInterface;

	/**
	 * Retrieves a CSRF <state> value for the given $service
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getCSRFState(string $service = null):string;

	/**
	 * Checks if a CSRF state for the given provider exists
	 */
	public function hasCSRFState(string $service = null):bool;

	/**
	 * Deletes a CSRF state for the given $service (and current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearCSRFState(string $service = null):OAuthStorageInterface;

	/**
	 * Deletes all stored CSRF states (for the current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAllCSRFStates():OAuthStorageInterface;

	/**
	 * Prepares an AccessToken for storage (serialize, encrypt etc.)
	 * and returns a value that is suited for the underlying storage engine
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function toStorage(AccessToken $token):mixed;

	/**
	 * Retrieves token data from the underlying storage engine
	 * (decrypt, unserialize etc.) and returns an AccessToken
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function fromStorage(mixed $data):AccessToken;

}
