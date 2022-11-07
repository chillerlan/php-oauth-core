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

/**
 * Implements ab anstract OAuth storage adapter
 */
abstract class OAuthStorageAbstract implements OAuthStorageInterface{
	use LoggerAwareTrait;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions|\chillerlan\Settings\SettingsContainerInterface
	 */
	protected SettingsContainerInterface $options;

	/**
	 * OAuthStorageAbstract constructor.
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
