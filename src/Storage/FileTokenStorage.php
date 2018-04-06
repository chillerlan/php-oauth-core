<?php
/**
 * Class FileTokenStorage
 *
 * @filesource   FileTokenStorage.php
 * @created      31.12.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Token;

/**
 *
 */
class FileTokenStorage extends TokenStorageAbstract{

	/**
	 * @param string                  $service
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function storeAccessToken(string $service, Token $token):TokenStorageInterface{
		// TODO: Implement storeAccessToken() method.
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Token
	 * @throws \chillerlan\OAuth\OAuthException
	 */
	public function retrieveAccessToken(string $service):Token{
		// TODO: Implement retrieveAccessToken() method.
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken(string $service):bool{
		// TODO: Implement hasAccessToken() method.
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAccessToken(string $service):TokenStorageInterface{
		// TODO: Implement clearAccessToken() method.
	}

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAllAccessTokens():TokenStorageInterface{
		// TODO: Implement clearAllAccessTokens() method.
	}

	/**
	 * @param string $service
	 * @param string $state
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function storeAuthorizationState(string $service, string $state):TokenStorageInterface{
		// TODO: Implement storeAuthorizationState() method.
	}

	/**
	 * @param string $service
	 *
	 * @return string
	 */
	public function retrieveAuthorizationState(string $service):string{
		// TODO: Implement retrieveAuthorizationState() method.
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAuthorizationState(string $service):bool{
		// TODO: Implement hasAuthorizationState() method.
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAuthorizationState(string $service):TokenStorageInterface{
		// TODO: Implement clearAuthorizationState() method.
	}

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAllAuthorizationStates():TokenStorageInterface{
		// TODO: Implement clearAllAuthorizationStates() method.
	}
}
