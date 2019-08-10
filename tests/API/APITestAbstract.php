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
use chillerlan\Settings\SettingsContainerInterface;
use chillerlan\OAuth\{Core\OAuthInterface, OAuthOptions};
use chillerlan\OAuthTest\{OAuthTestHttpClient, OAuthTestLogger, OAuthTestMemoryStorage};
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

use function chillerlan\HTTP\Psr7\{get_json, get_xml};

abstract class APITestAbstract extends TestCase{

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

	/**
	 * @var float
	 */
	protected $requestDelay = 0.25;

	/**
	 * a test username for live API tests, defined in .env as {ENV-PREFIX}_TESTUSER
	 *
	 * @var string
	 */
	protected $testuser;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * @var \Psr\Http\Client\ClientInterface
	 */
	protected $http;

	protected function setUp():void{
		\ini_set('date.timezone', 'Europe/Amsterdam');

		$file = \file_exists($this->CFG.'/.env') ? '.env' : '.env_travis';
		$this->dotEnv = (new DotEnv($this->CFG, $file))->load();

		if($this->dotEnv->get('IS_CI') === 'TRUE'){
			$this->markTestSkipped('not on CI');

			return;
		}

		$this->testuser = $this->dotEnv->get($this->ENV.'_TESTUSER');

		$options = [
			'key'              => $this->dotEnv->get($this->ENV.'_KEY'),
			'secret'           => $this->dotEnv->get($this->ENV.'_SECRET'),
			'tokenAutoRefresh' => true,
			// HTTPOptionsTrait
			'ca_info'          => $this->CFG.'/cacert.pem',
			'userAgent'        => 'chillerlanPhpOAuth/3.0.0 +https://github.com/chillerlan/php-oauth',
			// testHTTPClient
			'sleep'            => $this->requestDelay,
		];

		$this->options = new class($options) extends OAuthOptions{
			protected $sleep;
		};

		$this->logger   = new OAuthTestLogger('debug');
		$this->storage  = new OAuthTestMemoryStorage($this->options, $this->CFG);
		$this->http     = $this->initHttp($this->options, $this->logger);
		$this->provider = $this->getProvider();
	}

	/**
	 * @param \chillerlan\Settings\SettingsContainerInterface $options
	 * @param \Psr\Log\LoggerInterface                        $logger
	 *
	 * @return \Psr\Http\Client\ClientInterface
	 */
	protected function initHttp(SettingsContainerInterface $options, LoggerInterface $logger):ClientInterface{
		return new OAuthTestHttpClient($options, null, $logger);
	}

	/**
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected function getProvider():OAuthInterface{
		return new $this->FQN($this->http, $this->storage, $this->options, $this->logger);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \stdClass|mixed
	 */
	protected function responseJson(ResponseInterface $response){
		$response->getBody()->rewind();

		return get_json($response);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \SimpleXMLElement|mixed
	 */
	protected function responseXML(ResponseInterface $response){
		$response->getBody()->rewind();

		return get_xml($response);
	}

	public function testOAuthInstance(){
		$this->assertInstanceOf(OAuthInterface::class, $this->provider);
		$this->assertInstanceOf($this->FQN, $this->provider);
	}

}
