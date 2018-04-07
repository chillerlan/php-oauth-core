<?php
/**
 * Class MemoryTokenStorage
 *
 * @filesource   MemoryTokenStorage.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Token;

class MemoryTokenStorage extends TokenStorageAbstract{

	/**
	 * @var array
	 */
	protected $tokens = [];

	/**
	 * @var array
	 */
	protected $states = [];

	/**
	 * @param string                  $service
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function storeAccessToken(string $service, Token $token):TokenStorageInterface{
		$this->tokens[$service] = $token;

		return $this;
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Token
	 * @throws \chillerlan\OAuth\Storage\TokenStorageException
	 */
	public function getAccessToken(string $service):Token{

		if($this->hasAccessToken($service)){
			return $this->tokens[$service];
		}

		throw new TokenStorageException('token not found');
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken(string $service):bool {
		return isset($this->tokens[$service]) && $this->tokens[$service] instanceof Token;
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAccessToken(string $service):TokenStorageInterface{

		if(array_key_exists($service, $this->tokens)){
			unset($this->tokens[$service]);
		}

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAllAccessTokens():TokenStorageInterface{

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
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function storeCSRFState(string $service, string $state):TokenStorageInterface{
		$this->states[$service] = $state;

		return $this;
	}

	/**
	 * @param string $service
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Storage\TokenStorageException
	 */
	public function getCSRFState(string $service):string{

		if($this->hasCSRFState($service)){
			return $this->states[$service];
		}

		throw new TokenStorageException('state not found');
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
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearCSRFState(string $service):TokenStorageInterface{

		if(array_key_exists($service, $this->states)){
			unset($this->states[$service]);
		}

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAllCSRFStates():TokenStorageInterface{
		$this->states = [];

		return $this;
	}

}
