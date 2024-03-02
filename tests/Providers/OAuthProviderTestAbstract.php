<?php
/**
 * Class OAuthProviderTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\{OAuth1Interface, OAuth2Interface, OAuthInterface, TokenInvalidate};
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};
use chillerlan\Settings\SettingsContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\{NullHandler, StreamHandler};
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, ResponseFactoryInterface, StreamFactoryInterface, UriFactoryInterface};
use Psr\Log\LoggerInterface;
use Exception, ReflectionClass;
use function constant, defined, ini_set;
use const JSON_UNESCAPED_SLASHES;

abstract class OAuthProviderTestAbstract extends TestCase{

	protected bool $is_ci;

	// PSR interfaces
	protected RequestFactoryInterface  $requestFactory;
	protected ResponseFactoryInterface $responseFactory;
	protected StreamFactoryInterface   $streamFactory;
	protected UriFactoryInterface      $uriFactory;
	protected ClientInterface          $http;
	protected LoggerInterface          $logger;

	// OAuth related properties
	protected OAuthOptions|SettingsContainerInterface        $options;
	protected OAuthInterface|OAuth1Interface|OAuth2Interface $provider;
	protected OAuthStorageInterface                          $storage;
	protected ReflectionClass                                $reflection; // reflection of the test subject

	protected string $FQN; // fully qualified class name of the test subject
	protected array  $testProperties = [];
	protected array  $testResponses  = [];

	protected function setUp():void{
		ini_set('date.timezone', 'Europe/Amsterdam');

		// are we running on CI? (travis, github) -> see phpunit.xml
		$this->is_ci = defined('TEST_IS_CI') && constant('TEST_IS_CI') === true;

		// init PSR instances
		$this->initFactories(); // PSR-17 factories
		$this->logger = $this->initLogger($this->is_ci); // PSR-3 logger

		// init provider
		$this->options    = $this->initOptions();
		$this->storage    = $this->initStorage($this->options);
		$this->http       = $this->initHttp($this->options, $this->logger, $this->testResponses); // PSR-18 HTTP client
		$this->reflection = new ReflectionClass($this->FQN);
		$this->provider   = $this->initProvider();

		$this->initTestProperties($this->testProperties);
	}

	protected function initFactories():void{

		$factories = [
			'requestFactory'  => 'REQUEST_FACTORY',
			'responseFactory' => 'RESPONSE_FACTORY',
			'streamFactory'   => 'STREAM_FACTORY',
			'uriFactory'      => 'URI_FACTORY',
		];

		foreach($factories as $property => $const){

			if(!defined($const)){
				throw new Exception('constant "'.$const.'" not defined -> see phpunit.xml');
			}

			$this->{$property} = new (constant($const));
		}
	}

	protected function initLogger(bool $is_ci):LoggerInterface{
		$logger = new Logger('oauthProviderTest', [new NullHandler]);

		// logger output only when not on CI
		if(!$is_ci){
			$formatter = new LineFormatter(null, 'Y-m-d H:i:s', true, true);
			$formatter->setJsonPrettyPrint(true);
			$formatter->addJsonEncodeOption(JSON_UNESCAPED_SLASHES);

			$logger->pushHandler((new StreamHandler('php://stdout'))->setFormatter($formatter));
		}

		return $logger;
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

	protected function initHttp(SettingsContainerInterface $options, LoggerInterface $logger, array $responses):ClientInterface{
		return new ProviderTestHttpClient($responses, $this->responseFactory, $this->streamFactory);
	}

	protected function initProvider():OAuthInterface|OAuth1Interface|OAuth2Interface{
		$provider = $this->reflection->newInstanceArgs([$this->http, $this->options, $this->logger]);

		$provider
			->setStorage($this->storage)
			->setRequestFactory($this->requestFactory)
			->setStreamFactory($this->streamFactory)
			->setUriFactory($this->uriFactory)
		;

		return $provider;
	}

	protected function initTestProperties(array $properties):void{
		foreach($properties as $property => $value){
			$this->reflection->getProperty($property)->setValue($this->provider, $value);
		}
	}

	public function testOAuthInstance():void{
		$this::assertInstanceOf(OAuthInterface::class, $this->provider);
	}

	public function testProviderInstance():void{
		$this::assertInstanceOf($this->FQN, $this->provider);
	}

	public function testMagicGet():void{
		$this::assertSame($this->reflection->getShortName(), $this->provider->serviceName);
		/** @noinspection PhpUndefinedFieldInspection */
		$this::assertNull($this->provider->foo);
	}

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$this::assertTrue($this->storage->hasAccessToken());
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken());
	}

	public static function requestTargetProvider():array{
		return [
			'empty'          => ['', 'https://localhost/api'],
			'slash'          => ['/', 'https://localhost/api/'],
			'no slashes'     => ['a', 'https://localhost/api/a'],
			'leading slash'  => ['/b', 'https://localhost/api/b'],
			'trailing slash' => ['c/', 'https://localhost/api/c/'],
			'full url given' => ['https://localhost/other/path/d', 'https://localhost/other/path/d'],
			'ignore params'  => ['https://localhost/api/e/?with=param#foo', 'https://localhost/api/e/'],
		];
	}

	#[DataProvider('requestTargetProvider')]
	public function testGetRequestTarget(string $path, string $expected):void{
		$this->reflection->getProperty('apiURL')->setValue($this->provider, 'https://localhost/api/');
		$requestTarget = $this->reflection->getMethod('getRequestTarget')->invokeArgs($this->provider, [$path]);

		$this::assertSame($expected, $requestTarget);
	}

}
