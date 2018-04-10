<?php
/**
 * Class OAuthProvider
 *
 * @filesource   OAuthProvider.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Providers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\{
	HTTPClientInterface, HTTPClientTrait
};
use chillerlan\MagicAPI\ApiClientInterface;
use chillerlan\OAuth\Storage\TokenStorageInterface;
use chillerlan\Traits\{
	ClassLoader, ContainerInterface, Magic
};
use Psr\Log\{
	LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger
};
use ReflectionClass;

/**
 * @property string $serviceName
 * @property string $userRevokeURL
 */
abstract class OAuthProvider implements OAuthInterface, LoggerAwareInterface{
	use ClassLoader, Magic, HTTPClientTrait, LoggerAwareTrait;

	/**
	 * @var \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $serviceName;

	/**
	 * @var string
	 */
	protected $authURL;

	/**
	 * @var string
	 */
	protected $apiURL;

	/**
	 * @var string
	 */
	protected $userRevokeURL;

	/**
	 * @var string
	 */
	protected $revokeURL;

	/**
	 * @var string
	 */
	protected $accessTokenURL;

	/**
	 * @var array
	 */
	protected $authHeaders = [];

	/**
	 * @var array
	 */
	protected $apiHeaders = [];

	/**
	 * OAuthProvider constructor.
	 *
	 * @param \chillerlan\HTTP\HTTPClientInterface            $http
	 * @param \chillerlan\OAuth\Storage\TokenStorageInterface $storage
	 * @param \chillerlan\Traits\ContainerInterface           $options
	 * @param \Psr\Log\LoggerInterface|null                   $logger
	 */
	public function __construct(HTTPClientInterface $http, TokenStorageInterface $storage, ContainerInterface $options, LoggerInterface $logger = null){
		$this->setHTTPClient($http);

		$this->storage     = $storage;
		$this->options     = $options;
		$this->logger      = $logger ?? new NullLogger;

		$this->serviceName = (new ReflectionClass($this))->getShortName();

		if($this instanceof ApiClientInterface){
			$this->loadEndpoints();
		}

	}

	/**
	 * @return string
	 */
	protected function magic_get_serviceName():string {
		return $this->serviceName;
	}

	/**
	 * @return string
	 */
	protected function magic_get_userRevokeURL():string{
		return $this->userRevokeURL;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	public function getStorageInterface():TokenStorageInterface{
		return $this->storage;
	}

}
