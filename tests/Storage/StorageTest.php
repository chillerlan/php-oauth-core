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

use chillerlan\Database\{
	Database, DatabaseOptionsTrait, Drivers\MySQLiDrv
};
use chillerlan\OAuth\{
	Core\AccessToken, OAuthOptions
};
use chillerlan\OAuth\Storage\{
	DBStorage, MemoryStorage, OAuthStorageInterface, SessionStorage
};
use chillerlan\OAuthTest\OAuthTestAbstract;
use chillerlan\Traits\ClassLoader;

class StorageTest extends OAuthTestAbstract{
	use ClassLoader;

	protected const STORAGE_INTERFACES = [
		'MemoryStorage'  => [MemoryStorage::class],
		'SessionStorage' => [SessionStorage::class],
		'DBStorage'      => [DBStorage::class],
	];

	protected const TABLE_TOKEN    = 'dbstoragetest_token';
	protected const TABLE_PROVIDER = 'dbstoragetest_providers';

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

		$options = [
			// OAuthOptions
			'dbTokenTable'     => $this::TABLE_TOKEN,
			'dbProviderTable'  => $this::TABLE_PROVIDER,
			'storageCryptoKey' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f',
			'dbUserID' => 1,
			// DatabaseOptions
			'driver'           => MySQLiDrv::class,
			'host'             => $this->env->MYSQL_HOST,
			'port'             => $this->env->MYSQL_PORT,
			'database'         => $this->env->MYSQL_DATABASE,
			'username'         => $this->env->MYSQL_USERNAME,
			'password'         => $this->env->MYSQL_PASSWORD,
		];

		$this->options = new class($options) extends OAuthOptions{
			use DatabaseOptionsTrait;
		};

		$this->token = new \chillerlan\OAuth\Core\AccessToken(['accessToken' => 'foobar']);
	}

	protected function initStorage($storageInterface):void{
		$db = null;

		if($storageInterface === DBStorage::class){
			$db = new Database($this->options);

			$db->connect();
			$db->drop->table($this::TABLE_TOKEN)->query();
			$db->create
				->table($this::TABLE_TOKEN)
				->primaryKey('label')
				->varchar('label', 32, null, false)
				->int('user_id',10, null, false)
				->varchar('provider_id', 30, '', false)
				->text('token', null, true)
				->text('state')
				->int('expires',10, null, false)
				->query();

			$db->drop->table($this::TABLE_PROVIDER)->query();
			$db->create
				->table($this::TABLE_PROVIDER)
				->primaryKey('provider_id')
				->tinyint('provider_id',10, null, false, 'UNSIGNED AUTO_INCREMENT')
				->varchar('servicename', 30, '', false)
				->query();

			$db->insert
				->into($this::TABLE_PROVIDER)
				->values(['provider_id' => 42, 'servicename' => 'testService'])
				->query();

		}

		$this->storage = $this->loadClass($storageInterface, OAuthStorageInterface::class, $this->options, $db);
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
