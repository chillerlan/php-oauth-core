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
	HTTPClientTrait, HTTPClientInterface
};
use chillerlan\Logger\LogTrait;
use chillerlan\OAuth\{
	API\OAuthAPIClientException,
	Storage\TokenStorageInterface
};
use chillerlan\Traits\ContainerInterface;
use chillerlan\Traits\Magic;
use Psr\Log\LoggerAwareInterface;
use ReflectionClass;

/**
 * @property string $serviceName
 * @property string $userRevokeURL
 */
abstract class OAuthProvider implements OAuthInterface, LoggerAwareInterface{
	use Magic, HTTPClientTrait, LogTrait;

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
	 * @var \stdClass method => [url, method, mandatory_params, params_in_url]
	 */
	protected $apiMethods;

	/**
	 * OAuthProvider constructor.
	 *
	 * @param \chillerlan\HTTP\HTTPClientInterface            $http
	 * @param \chillerlan\OAuth\Storage\TokenStorageInterface $storage
	 * @param \chillerlan\Traits\ContainerInterface           $options
	 */
	public function __construct(HTTPClientInterface $http, TokenStorageInterface $storage, ContainerInterface $options){
		$this->setHTTPClient($http);

		$this->storage = $storage;
		$this->options = $options;

		$this->serviceName = (new ReflectionClass($this))->getShortName();

		// @todo
		$file = __DIR__.'/../API/'.$this->serviceName.'.json';

		if(is_file($file)){
			$this->apiMethods = json_decode(file_get_contents($file));
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

	/**
	 * ugly, isn't it?
	 * @todo WIP
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return \chillerlan\HTTP\HTTPResponseInterface|null
	 * @throws \chillerlan\OAuth\API\OAuthAPIClientException
	 */
	public function __call(string $name, array $arguments){
		if(array_key_exists($name, $this->apiMethods)){

			$m = $this->apiMethods->{$name};

			$endpoint      = $m->path ?? '/';
			$method        = $m->method ?? 'GET';
			$body          = null;
			$headers       = isset($m->headers) && is_object($m->headers) ? (array)$m->headers : [];
			$path_elements = $m->path_elements ?? [];
			$params_in_url = count($path_elements);
			$params        = $arguments[$params_in_url] ?? null;
			$urlparams     = array_slice($arguments,0 , $params_in_url);

			if($params_in_url > 0){

				if(count($urlparams) < $params_in_url){
					throw new OAuthAPIClientException('too few URL params, required: '.implode(', ', $path_elements));
				}

				$endpoint = sprintf($endpoint, ...$urlparams);
			}

			if(in_array($method, ['POST', 'PATCH', 'PUT', 'DELETE'])){
				$body = $arguments[$params_in_url + 1] ?? $params;

				if($params === $body){
					$params = null;
				}

				if(is_array($body) && isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'json') !== false){
					$body = json_encode($body);
				}

			}

			$params = $this->checkQueryParams($params);
			$body   = $this->checkQueryParams($body);

			// twitter is v picky
			if($this instanceof Twitter){
				$params = $this->checkQueryParams($params, true);
				$body   = $this->checkQueryParams($body, true);
			}

			$this->debug('OAuthProvider::__call() -> '.$this->serviceName.'::'.$name.'()', ['$endpoint' => $endpoint, '$params' => $params, '$method' => $method, '$body' => $body, '$headers' => $headers]);

			return $this->request($endpoint, $params, $method, $body, $headers);
		}

		return null;
	}

}
