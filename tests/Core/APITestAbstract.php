<?php
/**
 * Class APITestAbstract
 *
 * @filesource   APITestAbstract.php
 * @created      09.04.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\HTTP\{
	HTTPClientAbstract, HTTPClientInterface, HTTPResponseInterface, TinyCurlClient
};
use chillerlan\OAuth\{
	Core\AccessToken, Core\OAuthInterface, OAuthOptions, Storage\MemoryStorage, Storage\OAuthStorageInterface
};
use chillerlan\OAuthTest\OAuthTestAbstract;
use chillerlan\TinyCurl\Request;
use chillerlan\Traits\ContainerInterface;
use Psr\Log\LoggerInterface;

abstract class APITestAbstract extends OAuthTestAbstract{

	/**
	 * @var string
	 */
	protected $TOKEN_EXT = 'token.json';

	/**
	 * @var string
	 */
	protected $FQCN;

	/**
	 * @var \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected $provider;

	/**
	 * @var \chillerlan\HTTP\HTTPResponseInterface
	 */
	protected $response;

	/**
	 * @var \chillerlan\HTTP\HTTPClientInterface
	 */
	protected $http;

	/**
	 * this is ugly. don't look at it - it works.
	 */
	protected function setUp(){
		parent::setUp();

		if($this->isCI){
			$this->markTestSkipped('not on CI');
			return;
		}

		$options = [
			'key'              => $this->env->get($this->envvar.'_KEY'),
			'secret'           => $this->env->get($this->envvar.'_SECRET'),
			'tokenAutoRefresh' => true,
			// HTTPOptionsTrait
			'ca_info'          => $this->CFGDIR.'/cacert.pem',
			'userAgent'        => 'chillerlanPhpOAuth/3.0.0 +https://github.com/chillerlan/php-oauth',
			// testHTTPClient
			'sleep'            => 0.25,
		];

		$this->options = new class($options) extends OAuthOptions{
			protected $sleep;
		};

		$this->storage  = new MemoryStorage;
		$this->http     = $this->initHttp($this->options);
		$this->provider = $this->initProvider($this->http, $this->storage, $this->options, $this->logger);

		$tokenfile = $this->CFGDIR.'/'.$this->provider->serviceName.'.'.$this->TOKEN_EXT;

		$token = is_file($tokenfile)
			? (new AccessToken)->__fromJSON(file_get_contents($tokenfile))
			: new AccessToken(['accessToken' => '']);

		$this->storage->storeAccessToken($this->provider->serviceName, $token);
	}

	/**
	 * @param \chillerlan\HTTP\HTTPClientInterface            $http
	 * @param \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
	 * @param \chillerlan\Traits\ContainerInterface           $options
	 * @param \Psr\Log\LoggerInterface                        $logger
	 *
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected function initProvider(HTTPClientInterface $http, OAuthStorageInterface $storage, ContainerInterface $options, LoggerInterface $logger){
		return new $this->FQCN($http, $storage, $options, $logger);
	}

	/**
	 * @param \chillerlan\Traits\ContainerInterface $options
	 *
	 * @return \chillerlan\HTTP\HTTPClientInterface
	 */
	protected function initHttp(ContainerInterface $options):HTTPClientInterface{
		return new class($options) extends HTTPClientAbstract{
			protected $client;

			public function __construct(ContainerInterface $options){
				parent::__construct($options);
				$this->client = new TinyCurlClient($this->options, new Request($this->options));
			}

			protected function getResponse():HTTPResponseInterface{
				$response = $this->client->request($this->requestURL, $this->requestParams, $this->requestMethod, $this->requestBody, $this->requestHeaders);
				usleep($this->options->sleep * 1000000);
				return $response;
			}
		};
	}

	protected function tearDown(){
		if($this->response instanceof HTTPResponseInterface){

			$json = $this->response->json_array;

			!empty($json)
				? $this->logger->debug('tearDown() '.$this->response->url, $json)
				: $this->logger->debug('tearDown() '.$this->response->url, $this->response->body);
		}
	}

	public function testOAuthInstance(){
		$this->assertInstanceOf(OAuthInterface::class, $this->provider);
		$this->assertInstanceOf($this->FQCN, $this->provider);
	}

}
