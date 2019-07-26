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
use chillerlan\HTTP\Psr7;
use chillerlan\Settings\SettingsContainerInterface;
use chillerlan\OAuth\{Core\AccessToken, Core\OAuthInterface, OAuthOptions};
use chillerlan\OAuthTest\{OAuthTestHttpClient, OAuthTestLogger, OAuthTestMemoryStorage};
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

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

	protected function setUp():void{
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
			'sleep'            => $this->requestDelay,
		];

		$options = new class($options) extends OAuthOptions{
			protected $sleep;
		};

		$this->logger   = new OAuthTestLogger('debug');
		$http           = $this->initHttp($options, $this->logger);
		$this->storage  = new OAuthTestMemoryStorage($options, $this->CFG);
		$this->provider = new $this->FQN($http, $this->storage, $options, $this->logger);
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
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return mixed
	 */
	protected function responseJson(ResponseInterface $response){
		$response->getBody()->rewind();

		return Psr7\get_json($response);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return mixed
	 */
	protected function responseXML(ResponseInterface $response){
		$response->getBody()->rewind();

		return Psr7\get_xml($response);
	}

	public function testOAuthInstance(){
		$this->assertInstanceOf(OAuthInterface::class, $this->provider);
		$this->assertInstanceOf($this->FQN, $this->provider);
	}

}
