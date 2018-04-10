<?php
/**
 * Class StorageTest
 *
 * @filesource   StorageTest.php
 * @created      24.01.2018
 * @package      chillerlan\OAuthTest
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use chillerlan\Database\{
	Database, DatabaseOptionsTrait, Drivers\MySQLiDrv
};
use chillerlan\OAuth\{
	OAuthOptions, Token
};
use chillerlan\OAuth\Storage\{
	DBTokenStorage, MemoryTokenStorage, SessionTokenStorage, TokenStorageInterface
};
use chillerlan\Traits\{
	ClassLoader, DotEnv
};
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase{
	use ClassLoader;

	protected const STORAGE_INTERFACES = [
		'MemoryTokenStorage'  => [MemoryTokenStorage::class],
		'SessionTokenStorage' => [SessionTokenStorage::class],
		'DBTokenStorage'      => [DBTokenStorage::class],
	];

	protected const TABLE_TOKEN    = 'dbstoragetest_token';
	protected const TABLE_PROVIDER = 'dbstoragetest_providers';

	protected $CFGDIR = __DIR__.'/../config';

	/**
	 * @var \chillerlan\OAuth\Storage\TokenStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\Token
	 */
	protected $token;

	/**
	 * @var \chillerlan\Traits\DotEnv
	 */
	protected $env;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	protected function setUp(){
		$env = (new DotEnv($this->CFGDIR, file_exists($this->CFGDIR.'/.env') ? '.env' : '.env_travis'))->load();

		$options = [
			// OAuthOptions
			'dbTokenTable'     => $this::TABLE_TOKEN,
			'dbProviderTable'  => $this::TABLE_PROVIDER,
			'storageCryptoKey' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f',
			'dbUserID' => 1,
			// DatabaseOptions
			'driver'           => MySQLiDrv::class,
			'host'             => $env->MYSQL_HOST,
			'port'             => $env->MYSQL_PORT,
			'database'         => $env->MYSQL_DATABASE,
			'username'         => $env->MYSQL_USERNAME,
			'password'         => $env->MYSQL_PASSWORD,
		];

		$this->options = new class($options) extends OAuthOptions{
			use DatabaseOptionsTrait;
		};

		$this->token = new Token(['accessToken' => 'foobar']);
	}

	protected function initStorage($storageInterface):void{
		$db = null;

		if($storageInterface === DBTokenStorage::class){
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

		$this->storage = $this->loadClass($storageInterface, TokenStorageInterface::class, $this->options, $db);
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
	 * @expectedException \chillerlan\OAuth\Storage\TokenStorageException
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
	 * @expectedException \chillerlan\OAuth\Storage\TokenStorageException
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
		$this->assertInstanceOf(Token::class, $b);
		$this->assertEquals($this->token, $b);
	}

}
