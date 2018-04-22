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
use chillerlan\Traits\ContainerInterface;

class SessionStorage extends OAuthStorageAbstract{

	/**
	 * @var bool
	 */
	protected $sessionStart;

	/**
	 * @var string
	 */
	protected $sessionVar;

	/**
	 * @var string
	 */
	protected $stateVar;

	/**
	 * Session constructor.
	 *
	 * @param \chillerlan\Traits\ContainerInterface|null $options
	 */
	public function __construct(ContainerInterface $options = null){
		parent::__construct($options);

		$this->sessionVar = $this->options->sessionTokenVar;
		$this->stateVar = $this->options->sessionStateVar;

		if($this->options->sessionStart && !$this->sessionIsActive()){
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
	 * Destructor.
	 *
	 * @codeCoverageIgnore
	 */
	public function __destruct(){
		if($this->options->sessionStart){
			session_write_close();
		}
	}

	/**
	 * @param string                             $service
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function storeAccessToken(string $service, AccessToken $token):OAuthStorageInterface{
		$token = $token->__toJSON();

		if(isset($_SESSION[$this->sessionVar]) && is_array($_SESSION[$this->sessionVar])){
			$_SESSION[$this->sessionVar][$service] = $token;
		}
		else{
			$_SESSION[$this->sessionVar] = [$service => $token];
		}

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
			return (new AccessToken)->__fromJSON($_SESSION[$this->sessionVar][$service]);
		}

		throw new OAuthStorageException('token not found');
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken(string $service):bool{
		return isset($_SESSION[$this->sessionVar], $_SESSION[$this->sessionVar][$service]);
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAccessToken(string $service):OAuthStorageInterface{

		if(array_key_exists($service, $_SESSION[$this->sessionVar])){
			unset($_SESSION[$this->sessionVar][$service]);
		}

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAllAccessTokens():OAuthStorageInterface{

		foreach(array_keys($_SESSION[$this->sessionVar]) as $service){
			unset($_SESSION[$this->sessionVar][$service]); // trigger the memzero destructor
		}

		unset($_SESSION[$this->sessionVar]);

		return $this;
	}

	/**
	 * @param string $service
	 * @param string $state
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function storeCSRFState(string $service, string $state):OAuthStorageInterface{

		if(isset($_SESSION[$this->stateVar]) && is_array($_SESSION[$this->stateVar])){
			$_SESSION[$this->stateVar][$service] = $state;
		}
		else{
			$_SESSION[$this->stateVar] = [$service => $state];
		}

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
			return $_SESSION[$this->stateVar][$service];
		}

		throw new OAuthStorageException('state not found');
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasCSRFState(string $service):bool{
		return isset($_SESSION[$this->stateVar], $_SESSION[$this->stateVar][$service]);
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearCSRFState(string $service):OAuthStorageInterface{

		if(array_key_exists($service, $_SESSION[$this->stateVar])){
			unset($_SESSION[$this->stateVar][$service]);
		}

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAllCSRFStates():OAuthStorageInterface{
		unset($_SESSION[$this->stateVar]);

		return $this;
	}

	/**
	 * Determine if the session has started.
	 * @url http://stackoverflow.com/a/18542272/1470961
	 *
	 * @return bool
	 */
	public function sessionIsActive():bool{
		return session_status() !== PHP_SESSION_NONE;
	}

}
