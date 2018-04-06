<?php
/**
 * Class ProviderTestAbstract
 *
 * @filesource   ProviderTestAbstract.php
 * @created      22.10.2017
 * @package      chillerlan\OAuthTest\Providers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\HTTPClientInterface;
use chillerlan\OAuth\{
	OAuthOptions, Providers\OAuth2Interface, Providers\OAuthInterface, Storage\MemoryTokenStorage, Storage\TokenStorageInterface, Token
};
use PHPUnit\Framework\TestCase;
use ReflectionClass, ReflectionMethod, ReflectionProperty;

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
	 * @var \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\OAuth\Providers\OAuthInterface
	 */
	protected $provider;

	protected function setUp(){

		$this->options  = new OAuthOptions([
			'key'         => 'testkey',
			'secret'      => 'testsecret',
			'callbackURL' => 'https://localhost/callback',
		]);

		$this->storage    = new MemoryTokenStorage;
		$this->reflection = new ReflectionClass($this->FQCN);

		$this->provider = $this->reflection->newInstanceArgs([$this->initHttp(), $this->storage, $this->options]);
		$this->storage->storeAccessToken($this->provider->serviceName, new Token(['accessToken' => 'foo']));
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
	 * @param \chillerlan\OAuth\Token $token
	 */
	protected function storeToken(Token $token){
		$this->storage->storeAccessToken($this->provider->serviceName, $token);
	}

	public function testInstance(){
		$this->assertInstanceOf(OAuthInterface::class, $this->provider);
	}

	public function testMagicGetServicename(){
		$this->assertSame($this->reflection->getShortName(), $this->provider->serviceName);
	}

	public function testGetUserRevokeURL(){
		$this->setProperty($this->provider, 'userRevokeURL', '/oauth/revoke');

		$this->assertSame('/oauth/revoke', $this->provider->userRevokeURL);
	}

	public function testGetStorageInterface(){
		$this->assertInstanceOf(TokenStorageInterface::class, $this->provider->getStorageInterface());
	}

	// @todo
	public function testCall(){
		$this->setProperty($this->provider, 'apiMethods', json_decode('{"test":{"path":"","method":"POST"}}'));

		$this->assertSame('such data! much wow!', $this->provider->test()->json->data);
		$this->assertNull($this->provider->foo());
	}

	/**
	 * @expectedException \chillerlan\OAuth\API\OAuthAPIClientException
	 * @expectedExceptionMessage too few URL params, required:
	 */
	public function testCallTooFewPathElements(){
		$this->setProperty($this->provider, 'apiMethods', json_decode('{"test":{"path":"","path_elements":["foo"]}}'));


		$this->assertNull($this->provider->test());

	}

}
