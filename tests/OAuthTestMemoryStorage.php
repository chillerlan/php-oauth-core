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

	protected string $storagePath;

	public function __construct(OAuthOptions|SettingsContainerInterface $options = null, string $storagePath = null){
		parent::__construct($options);

		$this->storagePath = $storagePath ?? __DIR__;
	}

	/**
	 * @inheritDoc
	 */
	public function storeAccessToken(AccessToken $token, string $service = null):OAuthStorageInterface{
		parent::storeAccessToken($token, $service);

		$tokenFile = sprintf('%s/%s.token.json', $this->storagePath, $this->getServiceName($service));

		if(file_put_contents($tokenFile, $token->toJSON()) === false){
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

		$tokenFile = sprintf('%s/%s.token.json', $this->storagePath, $serviceName);

		if(file_exists($tokenFile)){
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return (new AccessToken)->fromJSON(file_get_contents($tokenFile));
		}

		throw new OAuthStorageException('token not found');
	}

}
