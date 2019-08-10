<?php
/**
 * Class ProviderTestAbstract
 *
 * @filesource   ProviderTestAbstract.php
 * @created      09.09.2018
 * @package      chillerlan\OAuthTest\Providers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\DotEnv\DotEnv;
use chillerlan\HTTP\Psr18\LoggingClient;
use chillerlan\HTTP\Psr7\Response;
use chillerlan\OAuth\{OAuthOptions, Storage\MemoryStorage};
use chillerlan\OAuth\Core\{AccessToken, OAuthInterface};
use chillerlan\OAuthTest\OAuthTestLogger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use ReflectionClass, ReflectionMethod, ReflectionProperty;

use function chillerlan\HTTP\Psr17\create_stream_from_input;
use function file_exists;

abstract class ProviderTestAbstract extends TestCase{

	/**
	 * @var string
	 */
	protected $CFG = __DIR__.'/../../config';

	/**
	 * @var string
	 */
	protected $FQN;

	/**
	 * @var \ReflectionClass
	 */
	protected $reflection;

	/**
	 * @var \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected $provider;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \chillerlan\DotEnv\DotEnv
	 */
	protected $dotEnv;

	/**
	 * @var bool
	 */
	protected $is_ci;

	protected function setUp():void{

		$file = file_exists($this->CFG.'/.env') ? '.env' : '.env_travis';

		$this->dotEnv = (new DotEnv($this->CFG, $file))->load();
		$this->is_ci  = $this->dotEnv->get('IS_CI') === 'TRUE';

		$this->options = new OAuthOptions([
			'key'              => 'testkey',
			'secret'           => 'testsecret',
			'callbackURL'      => 'https://localhost/callback',
			'tokenAutoRefresh' => true,
		]);

		$this->storage    = new MemoryStorage;
		$this->logger     = new OAuthTestLogger($this->is_ci ? 'none' : 'debug');
		$this->provider   = $this->getProvider();

		$this->storage->storeAccessToken($this->provider->serviceName, new AccessToken(['accessToken' => 'foo']));
	}

	abstract protected function getTestResponses():array;

	/**
	 * @return \Psr\Http\Client\ClientInterface
	 */
	protected function initHttp():ClientInterface{

		$client = new class($this->getTestResponses()) implements ClientInterface{

			/** @var array */
			protected $responses;

			public function __construct(array $responses){
				$this->responses = $responses;
			}

			public function sendRequest(RequestInterface $request):ResponseInterface{
				$stream = create_stream_from_input($this->responses[$request->getUri()->getPath()]);

				return (new Response)->withBody($stream);
			}

		};

		return new LoggingClient($client, $this->logger);
	}


	/**
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected function getProvider():OAuthInterface{
		$this->reflection = new ReflectionClass($this->FQN);

		return $this->reflection->newInstanceArgs([$this->initHttp(), $this->storage, $this->options, $this->logger]);
	}

	/**
	 * @param string $method
	 *
	 * @return \ReflectionMethod
	 */
	protected function getMethod(string $method):ReflectionMethod {
		$method = $this->reflection->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}

	/**
	 * @param string $property
	 *
	 * @return \ReflectionProperty
	 */
	protected function getProperty(string $property):ReflectionProperty{
		$property = $this->reflection->getProperty($property);
		$property->setAccessible(true);

		return $property;
	}

	/**
	 * @param        $object
	 * @param string $property
	 * @param        $value
	 *
	 * @return \ReflectionProperty
	 */
	protected function setProperty($object, string $property, $value):ReflectionProperty{
		$property = $this->getProperty($property);
		$property->setValue($object, $value);

		return $property;
	}

	public function testOAuthInstance(){
		$this->assertInstanceOf(OAuthInterface::class, $this->provider);
	}

	public function testMagicGet(){
		$this->assertSame($this->reflection->getShortName(), $this->provider->serviceName);
		$this->assertNull($this->provider->foo);
	}

}
