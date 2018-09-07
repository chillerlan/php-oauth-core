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

use chillerlan\OAuth\{Core\AccessToken, OAuthOptions};
use chillerlan\Settings\SettingsContainerInterface;

abstract class OAuthStorageAbstract implements OAuthStorageInterface{

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * OAuthStorageAbstract constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface|null $options
	 */
	public function __construct(SettingsContainerInterface $options = null){
		$this->options = $options ?? new OAuthOptions;
	}

	/**
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return string
	 */
	public function toStorage(AccessToken $token):string {
		$data = $token->toJSON();

		unset($token);

		return $data;
	}

	/**
	 * @param string $data
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function fromStorage(string $data):AccessToken{
		return (new AccessToken)->fromJSON($data);
	}

}
