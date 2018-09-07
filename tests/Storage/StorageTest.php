<?php
/**
 * Class StorageTest
 *
 * @filesource   StorageTest.php
 * @created      24.01.2018
 * @package      chillerlan\OAuthTest\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Storage\{MemoryStorage, SessionStorage};
use chillerlan\OAuthTest\OAuthTestAbstract;

class StorageTest extends OAuthTestAbstract{

	protected const STORAGE_INTERFACES = [
		'MemoryStorage'  => [MemoryStorage::class],
		'SessionStorage' => [SessionStorage::class],
	];

	/**
	 * @var \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\Core\AccessToken
	 */
	protected $token;

	protected function setUp(){
		parent::setUp();

		$this->token   = new AccessToken(['accessToken' => 'foobar']);
	}

	protected function initStorage($storageInterface):void{
		$this->storage = new $storageInterface;
	}

	/**
	 * @return array
	 */
	public function storageInterfaceProvider(){
		return $this::STORAGE_INTERFACES;
	}

	/**
	 * @dataProvider storageInterfaceProvider
	 * @runInSeparateProcess
	 *
	 * @param $storageInterface
	 */
	public function testTokenStorage($storageInterface){
		$this->initStorage($storageInterface);

		$this->storage->storeAccessToken('testService', $this->token);
		$this->assertTrue($this->storage->hasAccessToken('testService'));
		$this->assertSame('foobar', $this->storage->getAccessToken('testService')->accessToken);

		$this->storage->storeCSRFState('testService', 'foobar');
		$this->assertTrue($this->storage->hasCSRFState('testService'));
		$this->assertSame('foobar', $this->storage->getCSRFState('testService'));

		$this->storage->clearCSRFState('testService');
		$this->assertFalse($this->storage->hasCSRFState('testService'));

		$this->storage->clearAccessToken('testService');
		$this->assertFalse($this->storage->hasAccessToken('testService'));
	}

	/**
	 * @dataProvider storageInterfaceProvider
	 * @runInSeparateProcess
	 *
	 * @param $storageInterface
	 */
	public function testClearAllAccessTokens($storageInterface){
		$this->initStorage($storageInterface);

		$this->storage->clearAllAccessTokens();

		$this->assertFalse($this->storage->hasAccessToken('testService'));
		$this->storage->storeAccessToken('testService', $this->token);
		$this->assertTrue($this->storage->hasAccessToken('testService'));

		$this->assertFalse($this->storage->hasCSRFState('testService'));
		$this->storage->storeCSRFState('testService', 'foobar');
		$this->assertTrue($this->storage->hasCSRFState('testService'));

		$this->storage->clearAllCSRFStates();

		$this->assertFalse($this->storage->hasCSRFState('testService'));

		$this->storage->clearAllAccessTokens();

		$this->assertFalse($this->storage->hasAccessToken('testService'));
	}

	/**
	 * @expectedException \chillerlan\OAuth\Storage\OAuthStorageException
	 * @expectedExceptionMessage state not found
	 * @dataProvider storageInterfaceProvider
	 * @runInSeparateProcess
	 *
	 * @param $storageInterface
	 */
	public function testRetrieveCSRFStateNotFoundException($storageInterface){
		$this->initStorage($storageInterface);

		$this->storage->getCSRFState('LOLNOPE');
	}

	/**
	 * @expectedException \chillerlan\OAuth\Storage\OAuthStorageException
	 * @expectedExceptionMessage token not found
	 * @dataProvider storageInterfaceProvider
	 * @runInSeparateProcess
	 *
	 * @param $storageInterface
	 */
	public function testRetrieveAccessTokenNotFoundException($storageInterface){
		$this->initStorage($storageInterface);

		$this->storage->getAccessToken('LOLNOPE');
	}

	/**
	 * @dataProvider storageInterfaceProvider
	 * @runInSeparateProcess
	 *
	 * @param $storageInterface
	 */
	public function testToStorage($storageInterface){
		$this->initStorage($storageInterface);

		$a = $this->storage->toStorage($this->token);
		$b = $this->storage->fromStorage($a);

		$this->assertInternalType('string', $a);
		$this->assertInstanceOf(AccessToken::class, $b);
		$this->assertEquals($this->token, $b);
	}

}
