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

use chillerlan\HTTP\{Psr17, Psr7\Response};
use chillerlan\OAuth\Core\{AccessToken, OAuth1Interface, OAuth2Interface, OAuthInterface};
use chillerlan\OAuth\{OAuthOptions, Storage\MemoryStorage};
use Psr\Http\Client\ClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use ReflectionClass, ReflectionMethod, ReflectionProperty;

abstract class ProviderTestAbstract extends TestCase{

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
	 * @var array
	 */
	protected $responses;

	protected function setUp(){

		$this->options = new OAuthOptions([
			'key'              => 'testkey',
			'secret'           => 'testsecret',
			'callbackURL'      => 'https://localhost/callback',
			'tokenAutoRefresh' => true,
		]);

		$this->storage    = new MemoryStorage;
		$this->reflection = new ReflectionClass($this->FQN);
		$this->provider   = $this->reflection->newInstanceArgs([$this->initHttp(), $this->storage, $this->options]);

		$this->storage->storeAccessToken($this->provider->serviceName, new AccessToken(['accessToken' => 'foo']));
	}

	protected function initHttp():ClientInterface{
		return new class($this->reflection, $this->responses) implements ClientInterface{

			/** @var \ReflectionClass */
			protected $reflection;
			/** @var array */
			protected $responses;

			public function __construct(ReflectionClass $reflection, array $responses){
				$this->reflection = $reflection;
				$this->responses  = $responses;
			}

			public function sendRequest(RequestInterface $request):ResponseInterface{
				$path = $request->getUri()->getPath();
				$body = '';

				if($this->reflection->implementsInterface(OAuth1Interface::class)){
					$body = $this->responses[$path];

				}
				elseif($this->reflection->implementsInterface(OAuth2Interface::class)){
					$body = json_encode($this->responses[$path]);
				}

				return (new Response)->withBody(Psr17\create_stream_from_input($body));
			}
		};
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
