<?php
/**
 * Class StorageTestAbstract
 *
 * @filesource   StorageTestAbstract.php
 * @created      24.01.2018
 * @package      chillerlan\OAuthTest\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Storage\OAuthStorageException;
use PHPUnit\Framework\TestCase;

abstract class StorageTestAbstract extends TestCase{

	/**
	 * @var \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	protected $storage;

	/**
	 * @var \chillerlan\OAuth\Core\AccessToken
	 */
	protected $token;

	/**
	 * test service name
	 *
	 * @var string
	 */
	protected $tsn = 'testService';

	protected function setUp():void{
		$this->token = new AccessToken(['accessToken' => 'foobar']);
	}

	public function testTokenStorage(){

		$this->storage->storeAccessToken($this->tsn, $this->token);
		$this->assertTrue($this->storage->hasAccessToken($this->tsn));
		$this->assertSame('foobar', $this->storage->getAccessToken($this->tsn)->accessToken);

		$this->storage->storeCSRFState($this->tsn, 'foobar');
		$this->assertTrue($this->storage->hasCSRFState($this->tsn));
		$this->assertSame('foobar', $this->storage->getCSRFState($this->tsn));

		$this->storage->clearCSRFState($this->tsn);
		$this->assertFalse($this->storage->hasCSRFState($this->tsn));

		$this->storage->clearAccessToken($this->tsn);
		$this->assertFalse($this->storage->hasAccessToken($this->tsn));
	}

	public function testClearAllAccessTokens(){
		$this->storage->clearAllAccessTokens();

		$this->assertFalse($this->storage->hasAccessToken($this->tsn));
		$this->storage->storeAccessToken($this->tsn, $this->token);
		$this->assertTrue($this->storage->hasAccessToken($this->tsn));

		$this->assertFalse($this->storage->hasCSRFState($this->tsn));
		$this->storage->storeCSRFState($this->tsn, 'foobar');
		$this->assertTrue($this->storage->hasCSRFState($this->tsn));

		$this->storage->clearAllCSRFStates();

		$this->assertFalse($this->storage->hasCSRFState($this->tsn));

		$this->storage->clearAllAccessTokens();

		$this->assertFalse($this->storage->hasAccessToken($this->tsn));
	}

	public function testRetrieveCSRFStateNotFoundException(){
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('state not found');

		$this->storage->getCSRFState('LOLNOPE');
	}

	public function testRetrieveAccessTokenNotFoundException(){
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('token not found');

		$this->storage->getAccessToken('LOLNOPE');
	}

	public function testToStorage(){
		$a = $this->storage->toStorage($this->token);
		$b = $this->storage->fromStorage($a);

		$this->assertIsString($a);
		$this->assertInstanceOf(AccessToken::class, $b);
		$this->assertEquals($this->token, $b);
	}

	public function testStoreWithExistingToken(){
		$this->storage->storeAccessToken($this->tsn, $this->token);

		$this->token->extraParams = array_merge($this->token->extraParams, ['q' => 'u here?']);

		$this->storage->storeAccessToken($this->tsn, $this->token);

		$token = $this->storage->getAccessToken($this->tsn);

		$this->assertSame('u here?', $token->extraParams['q']);
	}

}
