<?php
/**
 * Class AccessToken
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\Settings\SettingsContainerAbstract;

use function time;

/**
 * Access token implementation for any OAuth version.
 *
 * // Oauth1
 * @property string $accessTokenSecret
 *
 * // Oauth1/2
 * @property string $accessToken
 * @property string $refreshToken
 * @property array  $extraParams
 * @property int    $expires
 * @property string $provider
 */
final class AccessToken extends SettingsContainerAbstract{

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
	 * the access token secret (OAuth1)
	 */
	protected ?string $accessTokenSecret = null;

	/**
	 * the oauth access token
	 */
	protected ?string $accessToken = null;

	/**
	 * an optional refresh token (OAuth2)
	 */
	protected ?string $refreshToken = null;

	/**
	 * the token expiration date/time
	 * @todo: change to DateInterval?
	 */
	protected ?int $expires = self::EOL_UNKNOWN;

	/**
	 * Additional token parameters supplied by the provider
	 */
	protected array $extraParams = [];

	/**
	 * the provider who issued this token
	 */
	protected ?string $provider = null;

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
	 * @param int|null $expires
	 *
	 * @return void
	 */
	protected function set_expires(int $expires = null):void{
		$this->setExpiry($expires);
	}

	/**
	 * @param int|null $expires
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 */
	public function setExpiry(int $expires = null):AccessToken{
		$now = time();

		if($expires === 0 || $expires === $this::EOL_NEVER_EXPIRES){
			$this->expires = $this::EOL_NEVER_EXPIRES;
		}
		elseif($expires > $now){
			$this->expires = $expires;
		}
		elseif($expires > 0 && $expires < $this::EXPIRY_MAX){
			$this->expires = $now + $expires;
		}
		else{
			$this->expires = $this::EOL_UNKNOWN;
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isExpired():bool{
		return $this->expires !== $this::EOL_NEVER_EXPIRES && $this->expires !== $this::EOL_UNKNOWN && time() > $this->expires;
	}

}
