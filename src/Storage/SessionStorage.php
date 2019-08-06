<?php
/**
 * Class SessionStorage
 *
 * @filesource   SessionStorage.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\Settings\SettingsContainerInterface;

use function array_keys, array_key_exists, is_array, session_start, session_status, session_write_close;

use const PHP_SESSION_NONE;

class SessionStorage extends OAuthStorageAbstract{

	/**
	 * @var string
	 */
	protected $sessionVar;

	/**
	 * @var string
	 */
	protected $stateVar;

	/**
	 * SessionStorage constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface|null $options
	 */
	public function __construct(SettingsContainerInterface $options = null){
		parent::__construct($options);

		$this->sessionVar = $this->options->sessionTokenVar;
		$this->stateVar   = $this->options->sessionStateVar;

		// Determine if the session has started.
		// @link http://stackoverflow.com/a/18542272/1470961
		if($this->options->sessionStart && !(session_status() !== PHP_SESSION_NONE)){
			session_start();
		}

		if(!isset($_SESSION[$this->sessionVar])){
			$_SESSION[$this->sessionVar] = [];
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
	public function storeAccessToken(string $service, AccessToken $token):bool{
		$_SESSION[$this->sessionVar][$service] = $this->toStorage($token);

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $service):AccessToken{

		if($this->hasAccessToken($service)){
			return $this->fromStorage($_SESSION[$this->sessionVar][$service]);
		}

		throw new OAuthStorageException('token not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasAccessToken(string $service):bool{
		return isset($_SESSION[$this->sessionVar], $_SESSION[$this->sessionVar][$service]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearAccessToken(string $service):bool{

		if(array_key_exists($service, $_SESSION[$this->sessionVar])){
			unset($_SESSION[$this->sessionVar][$service]);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllAccessTokens():bool{

		foreach(array_keys($_SESSION[$this->sessionVar]) as $service){
			unset($_SESSION[$this->sessionVar][$service]);
		}

		unset($_SESSION[$this->sessionVar]);

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function storeCSRFState(string $service, string $state):bool{
		$_SESSION[$this->stateVar][$service] = $state;

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSRFState(string $service):string{

		if($this->hasCSRFState($service)){
			return $_SESSION[$this->stateVar][$service];
		}

		throw new OAuthStorageException('state not found');
	}

	/**
	 * @inheritDoc
	 */
	public function hasCSRFState(string $service):bool{
		return isset($_SESSION[$this->stateVar], $_SESSION[$this->stateVar][$service]);
	}

	/**
	 * @inheritDoc
	 */
	public function clearCSRFState(string $service):bool{

		if(array_key_exists($service, $_SESSION[$this->stateVar])){
			unset($_SESSION[$this->stateVar][$service]);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function clearAllCSRFStates():bool{
		unset($_SESSION[$this->stateVar]);

		return true;
	}

}
