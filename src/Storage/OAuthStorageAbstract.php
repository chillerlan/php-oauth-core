<?php
/**
 * Class OAuthStorageAbstract
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Log\{LoggerAwareTrait, LoggerInterface, NullLogger};
use function is_string;

/**
 * Implements an abstract OAuth storage adapter
 */
abstract class OAuthStorageAbstract implements OAuthStorageInterface{
	use LoggerAwareTrait;

	protected OAuthOptions|SettingsContainerInterface $options;
	protected string $serviceName;

	/**
	 * OAuthStorageAbstract constructor.
	 */
	public function __construct(OAuthOptions|SettingsContainerInterface $options = null, LoggerInterface $logger = null){
		$this->options = ($options ?? new OAuthOptions);
		$this->logger  = ($logger ?? new NullLogger);
	}

	/**
	 * @inheritDoc
	 */
	public function setServiceName(string $service):static{
		$service = trim($service);

		if(empty($service)){
			throw new OAuthStorageException('service name must not be empty');
		}

		$this->serviceName = $service;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getServiceName(string $service = null):string{

		if($service === null && !isset($this->serviceName)){
			throw new OAuthStorageException('invalid service');
		}

		$name = trim($service ?? $this->serviceName);

		if(empty($name)){
			throw new OAuthStorageException('service name must not be empty');
		}

		return $name;
	}

	/**
	 * @inheritDoc
	 */
	public function toStorage(AccessToken $token):string{
		return $token->toJSON();
	}

	/**
	 * @inheritDoc
	 * @phan-suppress PhanTypeMismatchReturnSuperType
	 */
	public function fromStorage(mixed $data):AccessToken{

		if(!is_string($data)){
			throw new OAuthStorageException('invalid data');
		}

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return (new AccessToken)->fromJSON($data);
	}

}
