<?php
/**
 * Class APITestAbstract
 *
 * @filesource   APITestAbstract.php
 * @created      09.04.2018
 * @package      chillerlan\OAuthTest\API
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\HTTP\{
	HTTPClientAbstract, HTTPClientInterface, HTTPOptionsTrait, HTTPResponseInterface, TinyCurlClient
};
use chillerlan\Logger\{
	Log, LogOptionsTrait, Output\ConsoleLog, Output\LogOutputAbstract
};
use chillerlan\OAuth\{
	OAuthOptions, Providers\OAuthInterface, Storage\MemoryTokenStorage, Storage\TokenStorageInterface, Token
};
use chillerlan\TinyCurl\Request;
use chillerlan\Traits\{
	ContainerInterface, DotEnv
};
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

abstract class APITestAbstract extends TestCase{

	/**
	 * @var string
	 */
	protected $CFGDIR    = __DIR__.'/../../config';

	/**
	 * @var string
	 */
	protected $TOKEN_EXT = 'token.json';

	/**
	 * @var string
	 */
	protected $FQCN;

	/**
	 * @var string
	 */
	protected $envvar;

	/**
	 * @var \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\Providers\OAuthInterface
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
	 * @var \chillerlan\Traits\DotEnv
	 */
	protected $env;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * this is ugly. don't look at it - it works.
	 */
	protected function setUp(){
		ini_set('date.timezone', 'Europe/Amsterdam');

		$options = $this->getOptions($this->CFGDIR);

		$this->options = new class($options) extends OAuthOptions{
			use HTTPOptionsTrait, LogOptionsTrait;

			protected $sleep;
		};

		$this->logger = new Log;
		$this->logger->addInstance(new ConsoleLog($options), 'console');

		$this->storage  = new MemoryTokenStorage;
		$this->http     = $this->initHttp($this->options);
		$this->provider = $this->initProvider($this->http, $this->storage, $this->options, $this->logger);

		$tokenfile = $this->CFGDIR.'/'.$this->provider->serviceName.'.'.$this->TOKEN_EXT;

		$token = is_file($tokenfile)
			? (new Token)->__fromJSON(file_get_contents($tokenfile))
			: new Token(['accessToken' => '']);

		$this->storage->storeAccessToken($this->provider->serviceName, $token);
	}

	/**
	 * @param string $cfgdir
	 *
	 * @return array
	 */
	protected  function getOptions(string $cfgdir):array {
		$this->env = (new DotEnv($cfgdir, file_exists($cfgdir.'/.env') ? '.env' : '.env_travis'))->load();

		return [
			'key'              => $this->env->get($this->envvar.'_KEY'),
			'secret'           => $this->env->get($this->envvar.'_SECRET'),
			'tokenAutoRefresh' => true,
			// HTTPOptionsTrait
			'ca_info'          => $cfgdir.'/cacert.pem',
			'userAgent'        => 'chillerlanPhpOAuth/3.0.0 +https://github.com/chillerlan/php-oauth',
			// log
			'minLogLevel'      => 'debug',
			// testHTTPClient
			'sleep'            => 0.25,
		];
	}

	/**
	 * @param \chillerlan\HTTP\HTTPClientInterface            $http
	 * @param \chillerlan\OAuth\Storage\TokenStorageInterface $storage
	 * @param \chillerlan\Traits\ContainerInterface           $options
	 * @param \Psr\Log\LoggerInterface                        $logger
	 *
	 * @return \chillerlan\OAuth\Providers\OAuthInterface
	 */
	protected function initProvider(HTTPClientInterface $http, TokenStorageInterface $storage, ContainerInterface $options, LoggerInterface $logger){
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

			public function request(string $url, array $params = null, string $method = null, $body = null, array $headers = null):HTTPResponseInterface{
				$args = func_get_args();
				$response = $this->client->request(...$args);
				usleep($this->options->sleep * 1000000);
				return $response;
			}
		};
	}

	protected function tearDown(){
		if($this->response instanceof HTTPResponseInterface){

			$json = $this->response->json;

			!empty($json)
				? print_r($json)
				: print_r($this->response->body);
		}
	}

	public function testOAuthInstance(){
		$this->assertInstanceOf(OAuthInterface::class, $this->provider);
		$this->assertInstanceOf($this->FQCN, $this->provider);
	}

}
