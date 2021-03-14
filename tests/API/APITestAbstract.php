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
use function defined, file_exists, ini_set, realpath;
use const DIRECTORY_SEPARATOR;

abstract class APITestAbstract extends TestCase{

	protected string $CFG = __DIR__.'/../../config';

	protected string $FQN;

	protected string $ENV;

	protected ClientInterface $http;

	protected OAuthInterface $provider;

	protected OAuthStorageInterface $storage;

	protected LoggerInterface $logger;

	protected DotEnv $dotEnv;
	/** a test username for live API tests, defined in .env as {ENV-PREFIX}_TESTUSER*/
	protected string $testuser;
	/** @var \chillerlan\OAuth\OAuthOptions|\chillerlan\Settings\SettingsContainerInterface */
	protected SettingsContainerInterface $options;

	protected function setUp():void{
		ini_set('date.timezone', 'Europe/Amsterdam');

		$this->CFG    = realpath($this->CFG);
		$file         = file_exists($this->CFG.DIRECTORY_SEPARATOR.'.env') ? '.env' : '.env_example';
		$this->dotEnv = (new DotEnv($this->CFG, $file))->load();

		if(defined('TEST_IS_CI') && TEST_IS_CI === true){
			$this->markTestSkipped('not on CI (set TEST_IS_CI in phpunit.xml to "false" if you want to run live API tests)');
		}

		$this->testuser = (string)$this->dotEnv->get($this->ENV.'_TESTUSER');

		$this->options = new OAuthOptions([
			'key'              => $this->dotEnv->get($this->ENV.'_KEY'),
			'secret'           => $this->dotEnv->get($this->ENV.'_SECRET'),
			'tokenAutoRefresh' => true,
			// HTTPOptionsTrait
			'ca_info'          => $this->CFG.DIRECTORY_SEPARATOR.'cacert.pem',
			'userAgent'        => 'chillerlanPhpOAuth/4.0.0 +https://github.com/chillerlan/php-oauth-core',
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
	 * @return \stdClass|array|bool
	 */
	protected function responseJson(ResponseInterface $response){
		$response->getBody()->rewind();

		return get_json($response);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \SimpleXMLElement|array|bool
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
