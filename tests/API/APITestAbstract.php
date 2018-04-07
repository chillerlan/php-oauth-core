<?php
/**
 * Class APITestAbstract
 *
 * @filesource   APITestAbstract.php
 * @created      10.07.2017
 * @package      chillerlan\OAuthTest\API
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\Database\{
	Database, DatabaseOptionsTrait, Drivers\MySQLiDrv
};
use chillerlan\HTTP\{
	CurlClient, GuzzleClient, HTTPClientAbstract, HTTPClientInterface, HTTPOptionsTrait, HTTPResponseInterface, StreamClient, TinyCurlClient
};
use chillerlan\Logger\Log;
use chillerlan\Logger\LogOptions;
use chillerlan\Logger\Output\LogOutputAbstract;
use chillerlan\OAuth\{
	OAuthOptions, Providers\ClientCredentials, Providers\OAuth2Interface, Providers\OAuthInterface, Storage\DBTokenStorage, Token
};
use chillerlan\TinyCurl\Request;
use chillerlan\Traits\{
	ContainerInterface, DotEnv
};
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

abstract class APITestAbstract extends TestCase{

	protected $CFGDIR         = __DIR__.'/../../config';
	protected $STORAGE        = __DIR__.'/../../tokenstorage';

	const UA             = 'chillerlanPhpOAuth/2.0.0 +https://github.com/chillerlan/php-oauth';
	const SLEEP_SECONDS  = 1.0;
	const TABLE_TOKEN    = 'storagetest';
	const TABLE_PROVIDER = 'storagetest_providers';

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
	 * @var string
	 */
	protected $FQCN;

	/**
	 * @var \chillerlan\Traits\DotEnv
	 */
	protected $env;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $envvar;

	/**
	 * @var array
	 */
	protected $scopes = [];

	protected function setUp(){
		ini_set('date.timezone', 'Europe/Amsterdam');

		$this->env = (new DotEnv($this->CFGDIR, file_exists($this->CFGDIR.'/.env') ? '.env' : '.env_travis'))->load();

		$options = [
			'key'              => $this->env->get($this->envvar.'_KEY'),
			'secret'           => $this->env->get($this->envvar.'_SECRET'),
			'callbackURL'      => $this->env->get($this->envvar.'_CALLBACK_URL'),
			'dbTokenTable'     => $this::TABLE_TOKEN,
			'dbProviderTable'  => $this::TABLE_PROVIDER,
			'storageCryptoKey' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f',
			'dbUserID'         => 1,
			'tokenAutoRefresh' => true,
			// HTTPOptionsTrait
			'ca_info'          => $this->CFGDIR.'/cacert.pem',
			'userAgent'        => $this::UA,
			// DatabaseOptionsTrait
			'driver'           => MySQLiDrv::class,
			'host'             => $this->env->MYSQL_HOST,
			'port'             => $this->env->MYSQL_PORT,
			'database'         => $this->env->MYSQL_DATABASE,
			'username'         => $this->env->MYSQL_USERNAME,
			'password'         => $this->env->MYSQL_PASSWORD,
			// testHTTPClient
			'testclient'       => 'tinycurl',
		];

		$this->options  = new class($options) extends OAuthOptions{
			use HTTPOptionsTrait, DatabaseOptionsTrait;

			protected $testclient;
		};
		$this->storage  = new DBTokenStorage($this->options, new Database($this->options));
		$this->http     = $this->initHTTP();
		$this->provider = new $this->FQCN($this->http, $this->storage, $this->options, $this->scopes);

		$logger = (new Log)->addInstance(
			new class (new LogOptions(['minLogLevel' => LogLevel::DEBUG])) extends LogOutputAbstract{

				protected function __log(string $level, string $message, array $context = null):void{
					echo $message.PHP_EOL.print_r($context, true).PHP_EOL;
				}

			},
			'console'
		);


		$this->provider->setLogger($logger);
		$this->storage->storeAccessToken($this->provider->serviceName, $this->getToken());
	}

	protected function tearDown(){
		if($this->response instanceof HTTPResponseInterface){

			$json = $this->response->json;

			!empty($json)
				? print_r($json)
				: print_r($this->response->body);
		}
	}

	protected function initHTTP():HTTPClientInterface{
		return new class($this->options) extends HTTPClientAbstract{
			protected $client;

			public function __construct(ContainerInterface $options){
				parent::__construct($options);
				$this->client = call_user_func([$this, $this->options->testclient]);
			}

			public function request(string $url, array $params = null, string $method = null, $body = null, array $headers = null):HTTPResponseInterface{
				$args = func_get_args();
#	        	print_r($args);
				$response = $this->client->request(...$args);
#	        	print_r($response);
				usleep(APITestAbstract::SLEEP_SECONDS * 1000000);
				return $response;
			}

			protected function guzzle(){
				return new GuzzleClient($this->options, new Client(['cacert' => $this->options->ca_info, 'headers' => ['User-Agent' => $this->options->userAgent]]));
			}

			protected function tinycurl(){
				return new TinyCurlClient($this->options, new Request($this->options));
			}

			protected function curl(){
				return new CurlClient($this->options);
			}

			protected function stream(){
				return new StreamClient($this->options);
			}

		};
	}

	protected function getToken():Token{
		$file = $this->STORAGE.'/'.$this->provider->serviceName.'.token';

		if(is_file($file)){
			return (new Token)->__fromJSON(file_get_contents($file));
		}

		return new Token(['accessToken' => '']);
	}

	public function testInstance(){
		$this->assertInstanceOf(OAuthInterface::class, $this->provider);
		$this->assertInstanceOf($this->FQCN, $this->provider);
	}

	public function testRequestCredentialsToken(){

		if(!$this->provider instanceof OAuth2Interface){
			$this->markTestSkipped('OAuth2 only');
		}

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('not supported');
		}

		$token = $this->provider->getClientCredentialsToken();

		$this->assertInstanceOf(Token::class, $token);
		$this->assertInternalType('string', $token->accessToken);

		if($token->expires !== Token::EOL_NEVER_EXPIRES){
			$this->assertGreaterThan(time(), $token->expires);
		}

		print_r($token);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage not supported
	 */
	public function testRequestCredentialsTokenNotSupportedException(){

		if(!$this->provider instanceof OAuth2Interface){
			$this->markTestSkipped('OAuth2 only');
		}

		if($this->provider instanceof ClientCredentials){
			$this->markTestSkipped('does not apply');
		}

		$this->provider->getClientCredentialsToken();
	}

}
