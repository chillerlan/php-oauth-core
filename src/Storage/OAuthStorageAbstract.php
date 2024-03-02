<?php
/**
 * Class OAuthStorageAbstract
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Log\{LoggerInterface, NullLogger};
use function is_string, trim;

/**
 * Implements an abstract OAuth storage adapter
 */
abstract class OAuthStorageAbstract implements OAuthStorageInterface{

	protected string $serviceName;

	/**
	 * OAuthStorageAbstract constructor.
	 */
	public function __construct(
		protected OAuthOptions|SettingsContainerInterface $options = new OAuthOptions,
		protected LoggerInterface                         $logger = new NullLogger
	){

	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function setLogger(LoggerInterface $logger):static{
		$this->logger = $logger;

		return $this;
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
	public function getServiceName(string|null $service = null):string{

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
	 */
	public function fromStorage(mixed $data):AccessToken{

		if(!is_string($data)){
			throw new OAuthStorageException('invalid data');
		}

		return (new AccessToken)->fromJSON($data);
	}

}
