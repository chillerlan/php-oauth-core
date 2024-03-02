<?php
/**
 * Interface OAuthStorageInterface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;
use Psr\Log\LoggerInterface;

/**
 * Specifies the methods required for an OAuth storage adapter
 */
interface OAuthStorageInterface{

	/**
	 * Sets a logger. (LoggerAwareInterface is stupid)
	 */
	public function setLogger(LoggerInterface $logger):static;

	/**
	 * Sets the current service provider name
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function setServiceName(string $service):static;

	/**
	 * Gets the current service provider name
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getServiceName(string|null $service = null):string;

	/**
	 * Stores an AccessToken for the given $service
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function storeAccessToken(AccessToken $token, string|null $service = null):static;

	/**
	 * Retrieves an AccessToken for the given $service
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getAccessToken(string|null $service = null):AccessToken;

	/**
	 * Checks if a token for $service exists
	 */
	public function hasAccessToken(string|null $service = null):bool;

	/**
	 * Deletes the access token for a given $service (and current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAccessToken(string|null $service = null):static;

	/**
	 * Deletes all access tokens (for the current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAllAccessTokens():static;

	/**
	 * Stores a CSRF <state> value for the given $service
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function storeCSRFState(string $state, string|null $service = null):static;

	/**
	 * Retrieves a CSRF <state> value for the given $service
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getCSRFState(string|null $service = null):string;

	/**
	 * Checks if a CSRF state for the given provider exists
	 */
	public function hasCSRFState(string|null $service = null):bool;

	/**
	 * Deletes a CSRF state for the given $service (and current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearCSRFState(string|null $service = null):static;

	/**
	 * Deletes all stored CSRF states (for the current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAllCSRFStates():static;

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
