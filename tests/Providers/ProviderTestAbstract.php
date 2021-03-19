<?php
/**
 * Class ProviderTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\DotEnv\DotEnv;
use chillerlan\HTTP\Psr17\{RequestFactory, ResponseFactory, StreamFactory};
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\MemoryStorage;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\OAuthTest\OAuthTestLogger;
use chillerlan\Settings\SettingsContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass, ReflectionMethod, ReflectionProperty;

use function chillerlan\HTTP\Psr7\{get_json, get_xml};
use function defined, file_exists, ini_set, realpath;

use const DIRECTORY_SEPARATOR;

abstract class ProviderTestAbstract extends TestCase{

	/** @var \chillerlan\OAuth\OAuthOptions|\chillerlan\Settings\SettingsContainerInterface */
	protected SettingsContainerInterface $options;
	protected OAuthInterface $provider;
	protected OAuthStorageInterface $storage;
	protected DotEnv $dotEnv;

	// PSR interfaces
	protected RequestFactory $requestFactory;
	protected ResponseFactory $responseFactory;
	protected StreamFactory $streamFactory;
	protected ClientInterface $http;
	protected LoggerInterface $logger;

	protected ReflectionClass $reflection;
	// config dir & fqcn of the test subject
	protected string $CFG = __DIR__.'/../../config';
	protected string $FQN;
	protected bool $is_ci;
	protected array $testResponses = [];

	protected function setUp():void{
		ini_set('date.timezone', 'Europe/Amsterdam');

		// get the .env config
		$this->CFG    = realpath($this->CFG);
		$envFile      = file_exists($this->CFG.DIRECTORY_SEPARATOR.'.env') ? '.env' : '.env_example';
		$this->dotEnv = (new DotEnv($this->CFG, $envFile))->load();

		// are we running on CI? (travis, github) -> see phpunit.xml
		$this->is_ci = defined('TEST_IS_CI') && TEST_IS_CI === true;

		// logger output only when not on CI
		$this->logger = new OAuthTestLogger($this->is_ci ? 'none' : 'debug');

		// init some PSR-17 factories
		$this->requestFactory  = new RequestFactory;
		$this->responseFactory = new ResponseFactory;
		$this->streamFactory   = new StreamFactory;

		$this->options    = $this->initOptions();
		$this->storage    = $this->initStorage($this->options);
		$this->http       = $this->initHttp($this->options, $this->logger, $this->testResponses);
		$this->reflection = new ReflectionClass($this->FQN);
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->provider   = $this->reflection->newInstanceArgs([$this->http, $this->storage, $this->options, $this->logger]);
	}

	protected function initOptions():SettingsContainerInterface{
		return new OAuthOptions([
			'key'              => 'testkey',
			'secret'           => 'testsecret',
			'callbackURL'      => 'https://localhost/callback',
			'tokenAutoRefresh' => true,
		]);
	}

	protected function initStorage(SettingsContainerInterface $options):OAuthStorageInterface{
		return new MemoryStorage($options);
	}

	abstract protected function initHttp(
		SettingsContainerInterface $options,
		LoggerInterface $logger,
		array $responses
	):ClientInterface;

	public function testOAuthInstance():void{
		$this::assertInstanceOf(OAuthInterface::class, $this->provider);
	}

	public function testProviderInstance():void{
		$this::assertInstanceOf($this->FQN, $this->provider);
	}

	public function testMagicGet():void{
		$this::assertSame($this->reflection->getShortName(), $this->provider->serviceName);
		$this::assertNull($this->provider->foo);
	}

	protected function getMethod(string $method):ReflectionMethod{
		$method = $this->reflection->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}

	protected function getProperty(string $property):ReflectionProperty{
		$property = $this->reflection->getProperty($property);
		$property->setAccessible(true);

		return $property;
	}

	/**
	 * @param object $object
	 * @param string $property
	 * @param mixed  $value
	 *
	 * @return void
	 */
	protected function setProperty(object $object, string $property, $value):void{
		$property = $this->getProperty($property);
		$property->setValue($object, $value);
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

}
