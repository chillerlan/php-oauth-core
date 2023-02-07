<?php
/**
 * Class OAuthTestMemoryStorage
 *
 * @created      26.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageException, OAuthStorageInterface};
use chillerlan\Settings\SettingsContainerInterface;

use function file_exists, file_get_contents, file_put_contents;

final class OAuthTestMemoryStorage extends MemoryStorage{

	protected string $storagepath;

	public function __construct(SettingsContainerInterface $options = null, string $storagepath = null){
		parent::__construct($options);

		$this->storagepath = $storagepath ?? __DIR__;
	}

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(string $service, AccessToken $token):OAuthStorageInterface{
		parent::storeAccessToken($service, $token);

		if(file_put_contents($this->storagepath.'/'.$service.'.token.json', $token->toJSON()) === false){
			throw new OAuthStorageException('unable to access file storage');
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $service):AccessToken{

		if($this->hasAccessToken($service)){
			return $this->tokens[$service];
		}

		$tokenfile = $this->storagepath.'/'.$service.'.token.json';
		if(file_exists($tokenfile)){
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return (new AccessToken)->fromJSON(file_get_contents($tokenfile));
		}

		throw new OAuthStorageException('token not found');
	}

}
