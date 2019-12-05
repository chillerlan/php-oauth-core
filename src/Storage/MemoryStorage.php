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

use function array_keys, array_key_exists;

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
	public function storeAccessToken(string $service, AccessToken $token):bool{
		$this->tokens[$service] = $token;

		return true;
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
	public function clearAccessToken(string $service):bool{

		if(array_key_exists($service, $this->tokens)){
			unset($this->tokens[$service]);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():bool{

		foreach(array_keys($this->tokens) as $service){
			unset($this->tokens[$service]);
		}

		$this->tokens = [];

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $service, string $state):bool{
		$this->states[$service] = $state;

		return true;
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
	public function clearCSRFState(string $service):bool{

		if(array_key_exists($service, $this->states)){
			unset($this->states[$service]);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCSRFStates():bool{
		$this->states = [];

		return true;
	}

}
