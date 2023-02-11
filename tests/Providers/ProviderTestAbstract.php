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

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};
use chillerlan\Settings\SettingsContainerInterface;
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\{NullHandler, StreamHandler};
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, ResponseFactoryInterface, StreamFactoryInterface, UriFactoryInterface};
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use function constant;
use function defined;
use function ini_set;
use const JSON_UNESCAPED_SLASHES;

abstract class ProviderTestAbstract extends TestCase{

	protected const FACTORIES = [
		'requestFactory'  => 'REQUEST_FACTORY',
		'responseFactory' => 'RESPONSE_FACTORY',
		'streamFactory'   => 'STREAM_FACTORY',
		'uriFactory'      => 'URI_FACTORY',
	];

	protected OAuthOptions|SettingsContainerInterface $options;
	protected OAuthInterface $provider;
	protected OAuthStorageInterface $storage;
	protected ReflectionClass $reflection; // reflection of the test subject

	// PSR interfaces
	protected RequestFactoryInterface $requestFactory;
	protected ResponseFactoryInterface $responseFactory;
	protected StreamFactoryInterface $streamFactory;
	protected UriFactoryInterface $uriFactory;
	protected ClientInterface $http;
	protected LoggerInterface $logger;

	protected string $FQN; // the test subject
	protected bool $is_ci;
	protected array $testResponses = [];

	/**
	 * @throws \Exception
	 */
	protected function setUp():void{
		ini_set('date.timezone', 'Europe/Amsterdam');

		// are we running on CI? (travis, github) -> see phpunit.xml
		$this->is_ci = defined('TEST_IS_CI') && constant('TEST_IS_CI') === true;

		// logger output only when not on CI
		$this->logger = new Logger('oauthProviderTest', [new NullHandler]); // PSR-3

		if(!$this->is_ci){
			$formatter = new LineFormatter(null, 'Y-m-d H:i:s', true, true);
			$formatter->setJsonPrettyPrint(true);
			$formatter->addJsonEncodeOption(JSON_UNESCAPED_SLASHES);

			$this->logger->pushHandler((new StreamHandler('php://stdout'))->setFormatter($formatter));
		}

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
		$this->provider   = $this->reflection->newInstanceArgs([$this->http, $this->options, $this->logger]);

		$this->provider
			->setStorage($this->storage)
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
	 *
	 */
	protected function setProperty(object $object, string $property, mixed $value):void{
		$property = $this->getProperty($property);
		$property->setValue($object, $value);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \stdClass|array|bool
	 */
	protected function responseJson(ResponseInterface $response):mixed{
		return MessageUtil::decodeJSON($response);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 *
	 * @return \SimpleXMLElement|array|bool
	 */
	protected function responseXML(ResponseInterface $response):mixed{
		return MessageUtil::decodeXML($response);
	}

}
