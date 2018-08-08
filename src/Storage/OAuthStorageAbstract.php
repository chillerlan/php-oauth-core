<?php
/**
 * Class OAuthStorageAbstract
 *
 * @filesource   OAuthStorageAbstract.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\{
	Core\AccessToken, OAuthOptions
};
use chillerlan\Traits\ImmutableSettingsInterface;
use Psr\Log\{
	LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger
};

abstract class OAuthStorageAbstract implements OAuthStorageInterface, LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * OAuthStorageAbstract constructor.
	 *
	 * @param \chillerlan\Traits\ImmutableSettingsInterface|null $options
	 * @param \Psr\Log\LoggerInterface|null              $logger
	 */
	public function __construct(ImmutableSettingsInterface $options = null, LoggerInterface $logger = null){
		$this->options = $options ?? new OAuthOptions;
		$this->logger  = $logger ?? new NullLogger;
	}

	/**
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return string
	 */
	public function toStorage(AccessToken $token):string {
		$data = $token->__toJSON();

		unset($token);

		if($this->options->useEncryption === true){
			return $this->encrypt($data);
		}

		return $data;
	}

	/**
	 * @param string $data
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function fromStorage(string $data):AccessToken{

		if($this->options->useEncryption === true){
			$data = $this->decrypt($data);
		}

		return (new AccessToken)->__fromJSON($data);
	}

	/**
	 * @param string $data
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	protected function encrypt(string &$data):string {

		if(function_exists('sodium_crypto_secretbox')){
			$box = sodium_crypto_secretbox($data, $this::TOKEN_NONCE, sodium_hex2bin($this->options->storageCryptoKey));

			sodium_memzero($data);

			return sodium_bin2hex($box);
		}

		throw new OAuthStorageException('sodium not installed'); // @codeCoverageIgnore
	}

	/**
	 * @param string $box
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	protected function decrypt(string $box):string {

		if(function_exists('sodium_crypto_secretbox_open')){
			return sodium_crypto_secretbox_open(sodium_hex2bin($box), $this::TOKEN_NONCE, sodium_hex2bin($this->options->storageCryptoKey));
		}

		throw new OAuthStorageException('sodium not installed'); // @codeCoverageIgnore
	}

}
