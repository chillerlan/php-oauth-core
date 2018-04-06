<?php
/**
 * Class Token
 *
 * @filesource   Token.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth;

use chillerlan\Traits\{
	Container, ContainerInterface, Crypto\MemzeroDestructorTrait
};

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
class Token implements ContainerInterface{
	use MemzeroDestructorTrait, Container{
		__construct as constructContainer;
	}

	/**
	 * Denotes an unknown end of life time.
	 */
	const EOL_UNKNOWN = -9001;

	/**
	 * Denotes a token which never expires
	 */
	const EOL_NEVER_EXPIRES = -9002;

	/**
	 * defines a maximum expiry period (1 year)
	 */
	const EXPIRY_MAX = 86400 * 365;

	/**
	 * @var string
	 */
	protected $requestToken;

	/**
	 * @var string
	 */
	protected $requestTokenSecret;

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
	 * Token constructor.
	 *
	 * @param array|null $properties
	 */
	public function __construct(array $properties = null){
		$this->constructContainer($properties);

		$this->setExpiry($this->expires);
	}

	/**
	 * Token setter
	 *
	 * @param string $property
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function __set(string $property, $value){

		if(property_exists($this, $property)){
			$property === 'expires'
				? $this->setExpiry($value)
				: $this->{$property} = $value;
		}

	}

	/**
	 * @param int $expires
	 *
	 * @return \chillerlan\OAuth\Token
	 */
	public function setExpiry(int $expires = null):Token{
		$now = time();

		if($expires!== null){
			$expires =  intval($expires);
		}

		$this->expires = $this::EOL_UNKNOWN;

		if($expires === 0 || $expires === $this::EOL_NEVER_EXPIRES){
			$this->expires = $this::EOL_NEVER_EXPIRES;
		}
		elseif($expires > $now){
			$this->expires = $expires;
		}
		elseif($expires > 0 && $expires < $this::EXPIRY_MAX){
			$this->expires = $now + $expires;
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
