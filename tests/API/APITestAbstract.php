<?php
/**
 * Class APITestAbstract
 *
 * @created      08.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\DotEnv\DotEnv;
use chillerlan\OAuth\{Core\OAuthInterface, OAuthOptions, Storage\OAuthStorageInterface};
use chillerlan\OAuthTest\{OAuthTestHttpClient, OAuthTestLogger, OAuthTestMemoryStorage};
use chillerlan\Settings\SettingsContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

use function chillerlan\HTTP\Psr7\{get_json, get_xml};
use function file_exists, ini_set;

abstract class APITestAbstract extends TestCase{

	protected string $CFG = __DIR__.'/../../config';

	protected string $FQN;

	protected string $ENV;

	protected OAuthInterface $provider;

	protected OAuthStorageInterface $storage;

	protected LoggerInterface $logger;

	protected DotEnv $dotEnv;

	protected float $requestDelay = 0.25;
	/** a test username for live API tests, defined in .env as {ENV-PREFIX}_TESTUSER*/
	protected string $testuser;
	/** @var \chillerlan\OAuth\OAuthOptions|\chillerlan\Settings\SettingsContainerInterface */
	protected SettingsContainerInterface $options;

	protected ClientInterface $http;

	protected function setUp():void{
		ini_set('date.timezone', 'Europe/Amsterdam');

		$file         = file_exists($this->CFG.'/.env') ? '.env' : '.env_example';
		$this->dotEnv = (new DotEnv($this->CFG, $file))->load();
		$isCI         = defined('TEST_IS_CI') && TEST_IS_CI === true;

		if($isCI){
			$this->markTestSkipped('not on CI');

			return;
		}

		$this->testuser = $this->dotEnv->get($this->ENV.'_TESTUSER');

		$this->options = new OAuthOptions([
			'key'              => $this->dotEnv->get($this->ENV.'_KEY'),
			'secret'           => $this->dotEnv->get($this->ENV.'_SECRET'),
			'tokenAutoRefresh' => true,
			// HTTPOptionsTrait
			'ca_info'          => $this->CFG.'/cacert.pem',
			'userAgent'        => 'chillerlanPhpOAuth/3.0.0 +https://github.com/chillerlan/php-oauth',
			'sleep'            => $this->requestDelay,
		]);

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

	public function testOAuthInstance():void{
		static::assertInstanceOf(OAuthInterface::class, $this->provider);
		static::assertInstanceOf($this->FQN, $this->provider);
	}

}
