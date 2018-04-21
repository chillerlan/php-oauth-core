<?php
/**
 * Class ProviderTestAbstract
 *
 * @filesource   ProviderTestAbstract.php
 * @created      22.10.2017
 * @package      chillerlan\OAuthTest\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\HTTP\HTTPClientInterface;
use chillerlan\OAuth\{
	Core\AccessToken, OAuthOptions, Storage\MemoryStorage, Storage\OAuthStorageInterface
};
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

abstract class ProviderTestAbstract extends TestCase{

	/**
	 * @var string
	 */
	protected $FQCN;

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

	protected function setUp(){

		$this->options  = new OAuthOptions([
			'key'         => 'testkey',
			'secret'      => 'testsecret',
			'callbackURL' => 'https://localhost/callback',
		]);

		$this->storage    = new MemoryStorage;
		$this->reflection = new ReflectionClass($this->FQCN);

		$this->provider = $this->reflection->newInstanceArgs([$this->initHttp(), $this->storage, $this->options]);
		$this->storage->storeAccessToken($this->provider->serviceName, new AccessToken(['accessToken' => 'foo']));
	}

	abstract protected function initHttp():HTTPClientInterface;

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

	/**
	 * @param string $prop
	 * @param string $path
	 */
	protected function setURL(string $prop, string $path){
		$this->setProperty($this->provider, $prop, $path);
	}

	/**
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 */
	protected function storeToken(AccessToken $token){
		$this->storage->storeAccessToken($this->provider->serviceName, $token);
	}

	public function testInstance(){
		$this->assertInstanceOf(\chillerlan\OAuth\Core\OAuthInterface::class, $this->provider);
	}

	public function testMagicGetServicename(){
		$this->assertSame($this->reflection->getShortName(), $this->provider->serviceName);
	}

	public function testGetUserRevokeURL(){
		$this->setProperty($this->provider, 'userRevokeURL', '/oauth/revoke');

		$this->assertSame('/oauth/revoke', $this->provider->userRevokeURL);
	}

	public function testGetStorageInterface(){
		$this->assertInstanceOf(OAuthStorageInterface::class, $this->provider->getStorageInterface());
	}

}
