<?php
/**
 * Class APITestAbstract
 *
 * @filesource   APITestAbstract.php
 * @created      08.09.2018
 * @package      chillerlan\OAuthTest\API
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\DotEnv\DotEnv;
use chillerlan\HTTP\{CurlClient, HTTPOptionsTrait, Psr7};
use chillerlan\Logger\{Log, LogOptionsTrait, Output\LogOutputAbstract};
use chillerlan\OAuth\{OAuthOptionsTrait, Core\AccessToken, Core\OAuthInterface, Storage\MemoryStorage};
use chillerlan\Settings\{SettingsContainerAbstract, SettingsContainerInterface};
use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Log\LoggerInterface;

class APITestAbstract extends TestCase{

	/**
	 * @var string
	 */
	protected $CFG = __DIR__.'/../../config';

	/**
	 * @var string
	 */
	protected $FQN;

	/**
	 * @var string
	 */
	protected $ENV;

	/**
	 * @var \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected $provider;

	/**
	 * @var \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	protected $storage;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \chillerlan\DotEnv\DotEnv
	 */
	protected $dotEnv;

	protected function setUp(){
		ini_set('date.timezone', 'Europe/Amsterdam');

		$file = file_exists($this->CFG.'/.env') ? '.env' : '.env_travis';
		$this->dotEnv = (new DotEnv($this->CFG, $file))->load();

		if($this->dotEnv->get('IS_CI') === 'TRUE'){
			$this->markTestSkipped('not on CI');

			return;
		}

		$options = [
			'key'              => $this->dotEnv->get($this->ENV.'_KEY'),
			'secret'           => $this->dotEnv->get($this->ENV.'_SECRET'),
			'tokenAutoRefresh' => true,
			// HTTPOptionsTrait
			'ca_info'          => $this->CFG.'/cacert.pem',
			'userAgent'        => 'chillerlanPhpOAuth/3.0.0 +https://github.com/chillerlan/php-oauth',
			// testHTTPClient
			'sleep'            => 0.25,
			// logger
			'minLogLevel'      => 'debug',
		];

		$options = new class($options) extends SettingsContainerAbstract{
			use OAuthOptionsTrait, HTTPOptionsTrait, LogOptionsTrait;
			protected $sleep;
		};

		$this->logger   = $this->initLog($options);
		$http           = $this->initHttp($options, $this->logger);
		$this->storage  = new MemoryStorage;
		$this->provider = new $this->FQN($http, $this->storage, $options);
		$this->provider->setLogger($this->logger);

		$tokenfile = $this->CFG.'/'.$this->provider->serviceName.'.token.json';
		$token = is_file($tokenfile)
			? (new AccessToken)->fromJSON(file_get_contents($tokenfile))
			: new AccessToken(['accessToken' => 'nope']);

		$this->storage->storeAccessToken($this->provider->serviceName, $token);
	}

	/**
	 * @param $options
	 *
	 * @return \Psr\Log\LoggerInterface
	 */
	protected function initLog($options):LoggerInterface{

		return (new Log)->addInstance(
			new class($options) extends LogOutputAbstract{

				protected function __log(string $level, string $message, array $context = null):void{
					echo $message;//.PHP_EOL.print_r($context, true).PHP_EOL;
				}

			},
			'console'
		);
	}

	/**
	 * @param $options
	 *
	 * @return \Http\Client\HttpClient
	 */
	protected function initHttp($options, $logger):HttpClient{
		return new class($options, $logger) implements HttpClient{
			/** @var \Http\Client\HttpClient */
			protected $client;
			/** @var \chillerlan\Settings\SettingsContainerInterface  */
			protected $options;
			/** @var \Psr\Log\LoggerInterface  */
			protected $logger;

			public function __construct(SettingsContainerInterface $options, LoggerInterface $logger){
				$this->options = $options;
				$this->logger  = $logger;
				$this->client  = new CurlClient($this->options);
			}

			public function sendRequest(RequestInterface $request):ResponseInterface{
				usleep($this->options->sleep * 1000000);

				$response = $this->client->sendRequest($request);

				$this->logger->debug("\n-----REQUEST-----\n".Psr7\message_to_string($request));
				$this->logger->debug("\n-----RESPONSE-----\n".Psr7\message_to_string($response));

				$response->getBody()->rewind();
				return $response;
			}
		};
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return mixed
	 */
	protected function responseJson(ResponseInterface $response){
		$response->getBody()->rewind();

		return json_decode($response->getBody()->getContents());
	}

	public function testOAuthInstance(){
		$this->assertInstanceOf(OAuthInterface::class, $this->provider);
		$this->assertInstanceOf($this->FQN, $this->provider);
	}

}