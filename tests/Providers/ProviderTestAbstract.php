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
use Psr\Http\Message\{RequestFactoryInterface, ResponseFactoryInterface, StreamFactoryInterface, UriFactoryInterface};
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};
use chillerlan\OAuthTest\OAuthTestLogger;
use chillerlan\Settings\SettingsContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception, ReflectionClass, ReflectionMethod, ReflectionProperty;

use function chillerlan\HTTP\Utils\{get_json, get_xml};
use function constant, defined, file_exists, ini_set, realpath;

use const DIRECTORY_SEPARATOR;

abstract class ProviderTestAbstract extends TestCase{

	protected const FACTORIES = [
		'requestFactory'  => 'REQUEST_FACTORY',
		'responseFactory' => 'RESPONSE_FACTORY',
		'streamFactory'   => 'STREAM_FACTORY',
		'uriFactory'      => 'URI_FACTORY',
	];

	/** @var \chillerlan\OAuth\OAuthOptions|\chillerlan\Settings\SettingsContainerInterface */
	protected SettingsContainerInterface $options;
	protected OAuthInterface $provider;
	protected OAuthStorageInterface $storage;
	protected DotEnv $dotEnv;

	// PSR interfaces
	protected RequestFactoryInterface $requestFactory;
	protected ResponseFactoryInterface $responseFactory;
	protected StreamFactoryInterface $streamFactory;
	protected UriFactoryInterface $uriFactory;
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
		$this->is_ci = defined('TEST_IS_CI') && constant('TEST_IS_CI') === true;

		// logger output only when not on CI
		$this->logger = new OAuthTestLogger($this->is_ci ? 'none' : 'debug');

		// init some PSR-17 factories
		foreach($this::FACTORIES as $property => $const){

			if(!defined($const)){
				throw new Exception('constant "'.$const.'" not defined -> see phpunit.xml');
			}

			$class             = constant($const);
			$this->{$property} = new $class;
		}

		$this->options    = $this->initOptions();
		$this->storage    = $this->initStorage($this->options);
		$this->http       = $this->initHttp($this->options, $this->logger, $this->testResponses);
		$this->reflection = new ReflectionClass($this->FQN);
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->provider   = $this->reflection->newInstanceArgs([$this->http, $this->storage, $this->options, $this->logger]);

		$this->provider
			->setRequestFactory($this->requestFactory)
			->setStreamFactory($this->streamFactory)
			->setUriFactory($this->uriFactory)
		;
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
