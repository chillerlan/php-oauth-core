<?php
/**
 * Interface TokenStorageInterface
 *
 * @filesource   TokenStorageInterface.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Token;

interface TokenStorageInterface{

	/**
	 * @param string                  $service
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function storeAccessToken(string $service, Token $token):TokenStorageInterface;

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Token
	 * @throws \chillerlan\OAuth\OAuthException
	 */
	public function getAccessToken(string $service):Token;

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken(string $service):bool;

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAccessToken(string$service):TokenStorageInterface;

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAllAccessTokens():TokenStorageInterface;

	/**
	 * @param string $service
	 * @param string $state
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function storeCSRFState(string $service, string $state):TokenStorageInterface;

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
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearCSRFState(string $service):TokenStorageInterface;

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAllCSRFStates():TokenStorageInterface;

	/**
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return string
	 */
	public function toStorage(Token $token):string;

	/**
	 * @param string $data
	 *
	 * @return \chillerlan\OAuth\Token
	 */
	public function fromStorage(string $data):Token;

}
