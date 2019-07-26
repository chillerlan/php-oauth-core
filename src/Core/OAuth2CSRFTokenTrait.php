<?php
/**
 * Trait OAuth2CSRFTokenTrait
 *
 * @filesource   OAuth2CSRFTokenTrait.php
 * @created      17.03.2018
 * @package      chillerlan\OAuth\Core
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

/**
 * @implements \chillerlan\OAuth\Core\CSRFToken
 *
 * @property string                                          $serviceName
 * @property \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
 */
trait OAuth2CSRFTokenTrait{

	/**
	 * @param string|null $state
	 *
	 * @return void
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function checkState(string $state = null):void{

		if(empty($state) || !$this->storage->hasCSRFState($this->serviceName)){
			throw new ProviderException('invalid state for '.$this->serviceName);
		}

		$knownState = $this->storage->getCSRFState($this->serviceName);

		if(!hash_equals($knownState, $state)){
			throw new ProviderException('invalid CSRF state: '.$this->serviceName.' '.$state);
		}

	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	protected function setState(array $params):array{

		if(!isset($params['state'])){
			$params['state'] = sha1(random_bytes(256));
		}

		$this->storage->storeCSRFState($this->serviceName, $params['state']);

		return $params;
	}

}
