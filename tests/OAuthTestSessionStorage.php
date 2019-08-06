<?php
/**
 * Class OAuthTestSessionStorage
 *
 * @filesource   OAuthTestSessionStorage.php
 * @created      26.07.2019
 * @package      chillerlan\OAuthTest
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Storage\{OAuthStorageException, SessionStorage};
use chillerlan\Settings\SettingsContainerInterface;

class OAuthTestSessionStorage extends SessionStorage{

	/**
	 * @var string
	 */
	protected $storagepath;

	/**
	 * OAuthTestSessionStorage constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface|null $options
	 * @param string|null                                          $storagepath
	 */
	public function __construct(SettingsContainerInterface $options = null, string $storagepath = null){
		parent::__construct($options);

		$this->storagepath = $storagepath ?? __DIR__;
	}

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(string $service, AccessToken $token):bool{
		parent::storeAccessToken($service, $token);

		if(@\file_put_contents($this->storagepath.'/'.$service.'.token.json', $token->toJSON()) === false){
			throw new OAuthStorageException('unable to access file storage');
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $service):AccessToken{

		if($this->hasAccessToken($service)){
			return (new AccessToken)->fromJSON($_SESSION[$this->sessionVar][$service]);
		}

		$tokenfile = $this->storagepath.'/'.$service.'.token.json';
		if(\file_exists($tokenfile)){
			return (new AccessToken)->fromJSON(file_get_contents($tokenfile));
		}

		throw new OAuthStorageException('token not found');
	}

}
