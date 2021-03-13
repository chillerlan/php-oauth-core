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

use chillerlan\OAuth\{Core\AccessToken, OAuthOptions};
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Log\{LoggerAwareTrait, LoggerInterface, NullLogger};

use function is_string;

abstract class OAuthStorageAbstract implements OAuthStorageInterface{
	use LoggerAwareTrait;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions|\chillerlan\Settings\SettingsContainerInterface
	 */
	protected SettingsContainerInterface $options;

	/**
	 * OAuthStorageAbstract constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface|null $options
	 * @param \Psr\Log\LoggerInterface|null                        $logger
	 */
	public function __construct(SettingsContainerInterface $options = null, LoggerInterface $logger = null){
		$this->options = $options ?? new OAuthOptions;

		$this->setLogger($logger ?? new NullLogger);
	}

	/**
	 * @inheritDoc
	 *
	 * @return string
	 */
	public function toStorage(AccessToken $token):string{
		return $token->toJSON();
	}

	/**
	 * @inheritDoc
	 */
	public function fromStorage($data):AccessToken{

		if(!is_string($data)){
			throw new OAuthStorageException('invalid data');
		}

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return (new AccessToken)->fromJSON($data);
	}

}
