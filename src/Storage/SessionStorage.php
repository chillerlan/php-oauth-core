<?php
/**
 * Class SessionStorage
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\Settings\SettingsContainerInterface;
use function array_key_exists;
use function array_keys;
use function session_start;
use function session_status;
use function session_write_close;
use const PHP_SESSION_NONE;

/**
 * Implements a session storage adapter. Session storage is half persistent as tokens are stored for the duration of the session.
 */
class SessionStorage extends OAuthStorageAbstract{

	/**
	 * the key name for the token storage array in $_SESSION
	 */
	protected string $tokenVar;

	/**
	 * the key name for the CSRF token storage array in $_SESSION
	 */
	protected string $stateVar;

	/**
	 * SessionStorage constructor.
	 */
	public function __construct(OAuthOptions|SettingsContainerInterface $options = null){
		parent::__construct($options);

		$this->tokenVar = $this->options->sessionTokenVar;
		$this->stateVar = $this->options->sessionStateVar;

		// Determine if the session has started.
		// @link http://stackoverflow.com/a/18542272/1470961
		if($this->options->sessionStart && !(session_status() !== PHP_SESSION_NONE)){
			session_start();
		}

		if(!isset($_SESSION[$this->tokenVar])){
			$_SESSION[$this->tokenVar] = [];
		}

		if(!isset($_SESSION[$this->stateVar])){
			$_SESSION[$this->stateVar] = [];
		}

	}

	/**
	 * SessionStorage destructor.
	 *
	 * @codeCoverageIgnore
	 */
	public function __destruct(){
		if($this->options->sessionStart){
			session_write_close();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $service = null):static{
		$_SESSION[$this->tokenVar][$this->getServiceName($service)] = $this->toStorage($token);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $service = null):AccessToken{

		if($this->hasAccessToken($service)){
			return $this->fromStorage($_SESSION[$this->tokenVar][$this->getServiceName($service)]);
		}

		throw new OAuthStorageException('token not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasAccessToken(string $service = null):bool{
		return isset($_SESSION[$this->tokenVar], $_SESSION[$this->tokenVar][$this->getServiceName($service)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearAccessToken(string $service = null):static{
		$serviceName = $this->getServiceName($service);

		if(array_key_exists($serviceName, $_SESSION[$this->tokenVar])){
			unset($_SESSION[$this->tokenVar][$serviceName]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():static{

		foreach(array_keys($_SESSION[$this->tokenVar]) as $service){
			unset($_SESSION[$this->tokenVar][$service]);
		}

		unset($_SESSION[$this->tokenVar]);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $state, string $service = null):static{
		$_SESSION[$this->stateVar][$this->getServiceName($service)] = $state;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSRFState(string $service = null):string{

		if($this->hasCSRFState($service)){
			return $_SESSION[$this->stateVar][$this->getServiceName($service)];
		}

		throw new OAuthStorageException('state not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasCSRFState(string $service = null):bool{
		return isset($_SESSION[$this->stateVar], $_SESSION[$this->stateVar][$this->getServiceName($service)]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearCSRFState(string $service = null):static{
		$serviceName = $this->getServiceName($service);

		if(array_key_exists($serviceName, $_SESSION[$this->stateVar])){
			unset($_SESSION[$this->stateVar][$serviceName]);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCSRFStates():static{
		unset($_SESSION[$this->stateVar]);

		return $this;
	}

}
