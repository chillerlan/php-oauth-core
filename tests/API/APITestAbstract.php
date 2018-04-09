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
	HTTPClientAbstract, HTTPOptionsTrait, HTTPResponseInterface, TinyCurlClient
};
use chillerlan\Logger\{
	Log, LogOptions, Output\LogOutputAbstract
};
use chillerlan\OAuth\{
	OAuthOptions, Providers\ClientCredentials, Providers\OAuth2Interface, Providers\OAuthInterface, Storage\MemoryTokenStorage, Token
};
use chillerlan\TinyCurl\Request;
use chillerlan\Traits\{
	ContainerInterface, DotEnv
};
use PHPUnit\Framework\TestCase;

abstract class APITestAbstract extends TestCase{

	protected $CFGDIR    = __DIR__.'/../../config';
	protected $TOKEN_EXT = 'token.json';

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

	/**
	 * this is ugly. don't look at it - it works.
	 */
	protected function setUp(){
		ini_set('date.timezone', 'Europe/Amsterdam');

		$this->env = (new DotEnv($this->CFGDIR, file_exists($this->CFGDIR.'/.env') ? '.env' : '.env_travis'))->load();

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

		$this->options  = new class($options) extends OAuthOptions{
			use HTTPOptionsTrait;

			protected $sleep;
		};

		$logger = (new Log)->addInstance(
			new class (new LogOptions(['minLogLevel' => 'debug'])) extends LogOutputAbstract{

				protected function __log(string $level, string $message, array $context = null):void{
					echo $message.PHP_EOL.print_r($context, true).PHP_EOL;
				}

			},
			'console'
		);

		$this->http = new class($this->options) extends HTTPClientAbstract{
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

		$this->storage  = new MemoryTokenStorage;
		$this->provider = new $this->FQCN($this->http, $this->storage, $this->options, $this->scopes);

		/** @noinspection PhpUndefinedMethodInspection */
		$this->provider->setLogger($logger);

		$tokenfile = $this->CFGDIR.'/'.$this->provider->serviceName.'.'.$this->TOKEN_EXT;

		$token = is_file($tokenfile)
			? (new Token)->__fromJSON(file_get_contents($tokenfile))
			: new Token(['accessToken' => '']);

		$this->storage->storeAccessToken($this->provider->serviceName, $token);
	}

	protected function tearDown(){
		if($this->response instanceof HTTPResponseInterface){

			$json = $this->response->json;

			!empty($json)
				? print_r($json)
				: print_r($this->response->body);
		}
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
