<?php
/**
 * Class SessionTokenStorage
 *
 * @filesource   SessionTokenStorage.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Token;
use chillerlan\Traits\ContainerInterface;

/**
 * @todo: encryption?
 *      considerations:
 *      - the session is running through a session handler that already encrypts the session data. nothing to do here.
 *      - the session runs in memory - i think it's silly to encrypt there. sodium_memzero() galore!
 */
class SessionTokenStorage extends TokenStorageAbstract{

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
	 */
	public function __destruct(){
		if($this->options->sessionStart){
			session_write_close();
		}
	}

	/**
	 * @param string                  $service
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function storeAccessToken(string $service, Token $token):TokenStorageInterface{
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
	 * @return \chillerlan\OAuth\Token
	 * @throws \chillerlan\OAuth\Storage\TokenStorageException
	 */
	public function retrieveAccessToken(string $service):Token{

		if($this->hasAccessToken($service)){
			return (new Token)->__fromJSON($_SESSION[$this->sessionVar][$service]);
		}

		throw new TokenStorageException('token not found');
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
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAccessToken(string $service):TokenStorageInterface{

		if(array_key_exists($service, $_SESSION[$this->sessionVar])){
			unset($_SESSION[$this->sessionVar][$service]);
		}

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAllAccessTokens():TokenStorageInterface{

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
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function storeAuthorizationState(string $service, string $state):TokenStorageInterface{

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
	 * @throws \chillerlan\OAuth\Storage\TokenStorageException
	 */
	public function retrieveAuthorizationState(string $service):string{

		if($this->hasAuthorizationState($service)){
			return $_SESSION[$this->stateVar][$service];
		}

		throw new TokenStorageException('state not found');
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAuthorizationState(string $service):bool{
		return isset($_SESSION[$this->stateVar], $_SESSION[$this->stateVar][$service]);
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAuthorizationState(string $service):TokenStorageInterface{

		if(array_key_exists($service, $_SESSION[$this->stateVar])){
			unset($_SESSION[$this->stateVar][$service]);
		}

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function clearAllAuthorizationStates():TokenStorageInterface{
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
