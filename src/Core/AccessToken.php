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
 * // Oauth2
 * @property array  $scopes
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
	 * Denotes an unknown end of lifetime.
	 */
	public const EOL_UNKNOWN = -9001;

	/**
	 * Denotes a token which never expires
	 */
	public const EOL_NEVER_EXPIRES = -9002;

	/**
	 * Defines a maximum expiry period (1 year)
	 */
	public const EXPIRY_MAX = (86400 * 365);

	/**
	 * The access token secret (OAuth1)
	 */
	protected ?string $accessTokenSecret = null;

	/**
	 * The oauth access token
	 */
	protected ?string $accessToken = null;

	/**
	 * An optional refresh token (OAuth2)
	 */
	protected ?string $refreshToken = null;

	/**
	 * The token expiration date/time
	 * @todo: change to DateInterval?
	 */
	protected ?int $expires = self::EOL_UNKNOWN;

	/**
	 * Additional token parameters supplied by the provider
	 */
	protected array $extraParams = [];

	/**
	 * The scopes that are attached to this token (OAuth2)
	 *
	 * Please note that the scopes have to be stored manually after receiving the token
	 * as the initial auth URL request data is discarded before the callback comes in.
	 */
	protected array $scopes = [];

	/**
	 * The provider who issued this token
	 */
	protected ?string $provider = null;

	/**
	 * AccessToken constructor.
	 */
	public function __construct(iterable $properties = null){
		parent::__construct($properties);

		$this->setExpiry($this->expires);
	}

	/**
	 * Expiry setter
	 */
	protected function set_expires(int $expires = null):void{
		$this->setExpiry($expires);
	}

	/**
	 * Sets the expiration for this token
	 *
	 * @phan-suppress PhanPossiblyNullTypeMismatchProperty
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
			$this->expires = ($now + $expires);
		}
		else{
			$this->expires = $this::EOL_UNKNOWN;
		}

		return $this;
	}

	/**
	 * Checks whether this token is expired
	 */
	public function isExpired():bool{
		return $this->expires !== $this::EOL_NEVER_EXPIRES && $this->expires !== $this::EOL_UNKNOWN && time() > $this->expires;
	}

}
