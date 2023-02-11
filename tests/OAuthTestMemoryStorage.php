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
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageException, OAuthStorageInterface};
use chillerlan\Settings\SettingsContainerInterface;

use function file_exists, file_get_contents, file_put_contents;
use function sprintf;

/**
 * Extends the standard memory storage so that it also saves tokens as JSON in the given path
 */
final class OAuthTestMemoryStorage extends MemoryStorage{

	protected string $storagepath;

	public function __construct(OAuthOptions|SettingsContainerInterface $options = null, string $storagepath = null){
		parent::__construct($options);

		$this->storagepath = $storagepath ?? __DIR__;
	}

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $service = null):OAuthStorageInterface{
		parent::storeAccessToken($token, $service);

		$tokenfile = sprintf('%s/%s.token.json', $this->storagepath, $this->getServiceName($service));

		if(file_put_contents($tokenfile, $token->toJSON()) === false){
			throw new OAuthStorageException('unable to access file storage');
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $service = null):AccessToken{
		$serviceName = $this->getServiceName($service);

		if($this->hasAccessToken($service)){
			return $this->tokens[$serviceName];
		}

		$tokenfile = sprintf('%s/%s.token.json', $this->storagepath, $serviceName);

		if(file_exists($tokenfile)){
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return (new AccessToken)->fromJSON(file_get_contents($tokenfile));
		}

		throw new OAuthStorageException('token not found');
	}

}
