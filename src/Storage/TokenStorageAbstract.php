<?php
/**
 * Class TokenStorageAbstract
 *
 * @filesource   TokenStorageAbstract.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\{OAuthOptions, Token};
use chillerlan\Traits\ContainerInterface;
use Psr\Log\{
	LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger
};

abstract class TokenStorageAbstract implements TokenStorageInterface, LoggerAwareInterface{
	use LoggerAwareTrait;

	protected const TOKEN_NONCE = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01";

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * TokenStorageAbstract constructor.
	 *
	 * @param \chillerlan\Traits\ContainerInterface|null $options
	 * @param \Psr\Log\LoggerInterface|null              $logger
	 */
	public function __construct(ContainerInterface $options = null, LoggerInterface $logger = null){
		$this->options = $options ?? new OAuthOptions;
		$this->logger  = $logger ?? new NullLogger;
	}

	/**
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return string
	 */
	public function toStorage(Token $token):string {
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
	 * @return \chillerlan\OAuth\Token
	 */
	public function fromStorage(string $data):Token{

		if($this->options->useEncryption === true){
			$data = $this->decrypt($data);
		}

		return (new Token)->__fromJSON($data);
	}

	/**
	 * @param string $data
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Storage\TokenStorageException
	 */
	protected function encrypt(string &$data):string {

		if(function_exists('sodium_crypto_secretbox')){
			$box = sodium_crypto_secretbox($data, $this::TOKEN_NONCE, sodium_hex2bin($this->options->storageCryptoKey));

			sodium_memzero($data);

			return sodium_bin2hex($box);
		}

		throw new TokenStorageException('sodium not installed'); // @codeCoverageIgnore
	}

	/**
	 * @param string $box
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Storage\TokenStorageException
	 */
	protected function decrypt(string $box):string {

		if(function_exists('sodium_crypto_secretbox_open')){
			return sodium_crypto_secretbox_open(sodium_hex2bin($box), $this::TOKEN_NONCE, sodium_hex2bin($this->options->storageCryptoKey));
		}

		throw new TokenStorageException('sodium not installed'); // @codeCoverageIgnore
	}

}
