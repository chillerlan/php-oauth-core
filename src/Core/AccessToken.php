<?php
/**
 * Class AccessToken
 *
 * @filesource   AccessToken.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\Settings\SettingsContainerAbstract;

/**
 * Base token implementation for any OAuth version.
 *
 * // Oauth1
 * @property string $requestToken
 * @property string $requestTokenSecret
 * @property string $accessTokenSecret
 *
 * // Oauth1/2
 * @property string $accessToken
 * @property string $refreshToken
 * @property array  $extraParams
 * @property int    $expires
 * @property string $provider
 */
class AccessToken extends SettingsContainerAbstract{

	/**
	 * Denotes an unknown end of life time.
	 */
	public const EOL_UNKNOWN = -9001;

	/**
	 * Denotes a token which never expires
	 */
	public const EOL_NEVER_EXPIRES = -9002;

	/**
	 * defines a maximum expiry period (1 year)
	 */
	public const EXPIRY_MAX = 86400 * 365;

	/**
	 * @var string
	 */
	protected $accessTokenSecret;

	/**
	 * @var string
	 */
	protected $accessToken;

	/**
	 * @var string
	 */
	protected $refreshToken;

	/**
	 * @var int
	 */
	protected $expires = self::EOL_UNKNOWN;

	/**
	 * @var array
	 */
	protected $extraParams = [];

	/**
	 * the provider who issued this token
	 *
	 * @var string
	 */
	protected $provider;

	/**
	 * AccessToken constructor.
	 *
	 * @param iterable|null $properties
	 */
	public function __construct(iterable $properties = null){
		parent::__construct($properties);

		$this->setExpiry($this->expires);
	}

	/**
	 * AccessToken setter
	 *
	 * @param int|null $expires
	 *
	 * @return void
	 */
	protected function set_expires(int $expires = null):void{
		$this->setExpiry($expires);
	}

	/**
	 * @param int $expires
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function setExpiry(int $expires = null):AccessToken{
		$now = time();

		if($expires!== null){
			$expires = intval($expires);
		}

		$this->expires = self::EOL_UNKNOWN;

		if($expires === 0 || $expires === self::EOL_NEVER_EXPIRES){
			$this->expires = self::EOL_NEVER_EXPIRES;
		}
		elseif($expires > $now){
			$this->expires = $expires;
		}
		elseif($expires > 0 && $expires < self::EXPIRY_MAX){
			$this->expires = $now + $expires;
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isExpired():bool{
		return $this->expires !== self::EOL_NEVER_EXPIRES && $this->expires !== self::EOL_UNKNOWN && time() > $this->expires;
	}

}
