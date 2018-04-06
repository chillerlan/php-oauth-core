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

	const CFGDIR         = __DIR__.'/../config';
	const SERVICE_NAME   = 'Spotify';
	const TABLE_TOKEN    = 'storagetest';
	const TABLE_PROVIDER = 'storagetest_providers';

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
		$env = (new DotEnv(self::CFGDIR, file_exists(self::CFGDIR.'/.env') ? '.env' : '.env_travis'))->load();

		$options = [
			// OAuthOptions
			'dbTokenTable'     => StorageTest::TABLE_TOKEN,
			'dbProviderTable'  => StorageTest::TABLE_PROVIDER,
			'storageCryptoKey' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f',
			'dbUserID' => 1,
			// DatabaseOptions
			'driver'           => MySQLiDrv::class,
			'host'             => $env->get('MYSQL_HOST'),
			'port'             => $env->get('MYSQL_PORT'),
			'database'         => $env->get('MYSQL_DATABASE'),
			'username'         => $env->get('MYSQL_USERNAME'),
			'password'         => $env->get('MYSQL_PASSWORD'),
		];

		$this->options = new class($options) extends OAuthOptions{
			use DatabaseOptionsTrait;
		};

		$this->token   = new Token(['accessToken' => 'foobar']);
	}

	protected function initStorage($storageInterface):void{
		$p2 = null;

		if($storageInterface === DBTokenStorage::class){
			$p2 = new Database($this->options);
		}

		$this->storage = $this->loadClass($storageInterface, TokenStorageInterface::class, $this->options, $p2);
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

		$this->storage->storeAccessToken(self::SERVICE_NAME, $this->token);
		$this->assertTrue($this->storage->hasAccessToken(self::SERVICE_NAME));
		$this->assertSame('foobar', $this->storage->retrieveAccessToken(self::SERVICE_NAME)->accessToken);

		$this->storage->storeAuthorizationState(self::SERVICE_NAME, 'foobar');
		$this->assertTrue($this->storage->hasAuthorizationState(self::SERVICE_NAME));
		$this->assertSame('foobar', $this->storage->retrieveAuthorizationState(self::SERVICE_NAME));

		$this->storage->clearAuthorizationState(self::SERVICE_NAME);
		$this->assertFalse($this->storage->hasAuthorizationState(self::SERVICE_NAME));

		$this->storage->clearAccessToken(self::SERVICE_NAME);
		$this->assertFalse($this->storage->hasAccessToken(self::SERVICE_NAME));
	}

	/**
	 * @dataProvider storageInterfaceProvider
	 * @runInSeparateProcess
	 *
	 * @param $storageInterface
	 */
	public function testClearAllAccessTokens($storageInterface){
		$this->initStorage($storageInterface);

		$range = ['Spotify', 'LastFM', 'Twitter'];
		$this->storage->clearAllAccessTokens();

		foreach($range as $k){
			$this->assertFalse($this->storage->hasAccessToken($k));
			$this->storage->storeAccessToken($k, $this->token);
			$this->assertTrue($this->storage->hasAccessToken($k));
		}

		foreach($range as $k){
			$this->assertFalse($this->storage->hasAuthorizationState($k));
			$this->storage->storeAuthorizationState($k, 'foobar');
			$this->assertTrue($this->storage->hasAuthorizationState($k));
		}

		$this->storage->clearAllAuthorizationStates();

		foreach($range as $k){
			$this->assertFalse($this->storage->hasAuthorizationState($k));
		}

		$this->storage->clearAllAccessTokens();

		foreach($range as $k){
			$this->assertFalse($this->storage->hasAccessToken($k));
		}

	}

	/**
	 * @expectedException \chillerlan\OAuth\Storage\TokenStorageException
	 * @expectedExceptionMessage state not found
	 * @dataProvider storageInterfaceProvider
	 * @runInSeparateProcess
	 *
	 * @param $storageInterface
	 */
	public function testRetrieveAuthorizationStateNotFoundException($storageInterface){
		$this->initStorage($storageInterface);

		$this->storage->retrieveAuthorizationState('LOLNOPE');
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

		$this->storage->retrieveAccessToken('LOLNOPE');
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
