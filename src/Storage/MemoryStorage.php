<?php
/**
 * Class MemoryStorage
 *
 * @filesource   MemoryStorage.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;

class MemoryStorage extends OAuthStorageAbstract{

	/**
	 * @var array
	 */
	protected $tokens = [];

	/**
	 * @var array
	 */
	protected $states = [];

	/**
	 * @param string                             $service
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function storeAccessToken(string $service, AccessToken $token):OAuthStorageInterface{
		$this->tokens[$service] = $token;

		return $this;
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getAccessToken(string $service):AccessToken{

		if($this->hasAccessToken($service)){
			return $this->tokens[$service];
		}

		throw new OAuthStorageException('token not found');
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken(string $service):bool {
		return isset($this->tokens[$service]) && $this->tokens[$service] instanceof AccessToken;
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAccessToken(string $service):OAuthStorageInterface{

		if(array_key_exists($service, $this->tokens)){
			unset($this->tokens[$service]);
		}

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAllAccessTokens():OAuthStorageInterface{

		foreach(array_keys($this->tokens) as $service){
			unset($this->tokens[$service]); // trigger the memzero destructor
		}

		$this->tokens = [];

		return $this;
	}

	/**
	 * @param string $service
	 * @param string $state
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function storeCSRFState(string $service, string $state):OAuthStorageInterface{
		$this->states[$service] = $state;

		return $this;
	}

	/**
	 * @param string $service
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getCSRFState(string $service):string{

		if($this->hasCSRFState($service)){
			return $this->states[$service];
		}

		throw new OAuthStorageException('state not found');
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasCSRFState(string $service):bool {
		return isset($this->states[$service]) && null !== $this->states[$service];
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearCSRFState(string $service):OAuthStorageInterface{

		if(array_key_exists($service, $this->states)){
			unset($this->states[$service]);
		}

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAllCSRFStates():OAuthStorageInterface{
		$this->states = [];

		return $this;
	}

}
