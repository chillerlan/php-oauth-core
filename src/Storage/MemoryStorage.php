<?php
/**
 * Class MemoryStorage
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;
use function array_key_exists;
use function array_keys;

/**
 * Implements a memory storage adapter. Memory storage is not persistent as tokens are only stored during script runtime.
 */
class MemoryStorage extends OAuthStorageAbstract{

	/**
	 * the token storage array
	 */
	protected array $tokens = [];

	/**
	 * the CSRF state storage array
	 */
	protected array $states = [];

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(string $service, AccessToken $token):OAuthStorageInterface{
		$this->tokens[$service] = $token;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $service):AccessToken{

		if($this->hasAccessToken($service)){
			return $this->tokens[$service];
		}

		throw new OAuthStorageException('token not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasAccessToken(string $service):bool{
		return isset($this->tokens[$service]) && $this->tokens[$service] instanceof AccessToken;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAccessToken(string $service):OAuthStorageInterface{

		if(array_key_exists($service, $this->tokens)){
			unset($this->tokens[$service]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():OAuthStorageInterface{

		foreach(array_keys($this->tokens) as $service){
			unset($this->tokens[$service]);
		}

		$this->tokens = [];

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $service, string $state):OAuthStorageInterface{
		$this->states[$service] = $state;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSRFState(string $service):string{

		if($this->hasCSRFState($service)){
			return $this->states[$service];
		}

		throw new OAuthStorageException('state not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasCSRFState(string $service):bool{
		return isset($this->states[$service]) && null !== $this->states[$service];
	}

	/**
	 * @inheritDoc
	 */
	public function clearCSRFState(string $service):OAuthStorageInterface{

		if(array_key_exists($service, $this->states)){
			unset($this->states[$service]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCSRFStates():OAuthStorageInterface{
		$this->states = [];

		return $this;
	}

}
