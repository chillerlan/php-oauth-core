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
use chillerlan\OAuth\Storage\{OAuthStorageException, OAuthStorageInterface};
use PHPUnit\Framework\TestCase;

abstract class StorageTestAbstract extends TestCase{

	protected OAuthStorageInterface $storage;

	protected AccessToken $token;

	/**
	 * test service name
	 */
	protected string $tsn = 'testService';

	protected function setUp():void{
		$this->token = new AccessToken(['accessToken' => 'foobar']);
	}

	public function testTokenStorage():void{

		$this->storage->storeAccessToken($this->tsn, $this->token);
		static::assertTrue($this->storage->hasAccessToken($this->tsn));
		static::assertSame('foobar', $this->storage->getAccessToken($this->tsn)->accessToken);

		$this->storage->storeCSRFState($this->tsn, 'foobar');
		static::assertTrue($this->storage->hasCSRFState($this->tsn));
		static::assertSame('foobar', $this->storage->getCSRFState($this->tsn));

		$this->storage->clearCSRFState($this->tsn);
		static::assertFalse($this->storage->hasCSRFState($this->tsn));

		$this->storage->clearAccessToken($this->tsn);
		static::assertFalse($this->storage->hasAccessToken($this->tsn));
	}

	public function testClearAllAccessTokens():void{
		$this->storage->clearAllAccessTokens();

		static::assertFalse($this->storage->hasAccessToken($this->tsn));
		$this->storage->storeAccessToken($this->tsn, $this->token);
		static::assertTrue($this->storage->hasAccessToken($this->tsn));

		static::assertFalse($this->storage->hasCSRFState($this->tsn));
		$this->storage->storeCSRFState($this->tsn, 'foobar');
		static::assertTrue($this->storage->hasCSRFState($this->tsn));

		$this->storage->clearAllCSRFStates();

		static::assertFalse($this->storage->hasCSRFState($this->tsn));

		$this->storage->clearAllAccessTokens();

		static::assertFalse($this->storage->hasAccessToken($this->tsn));
	}

	public function testRetrieveCSRFStateNotFoundException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('state not found');

		$this->storage->getCSRFState('LOLNOPE');
	}

	public function testRetrieveAccessTokenNotFoundException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('token not found');

		$this->storage->getAccessToken('LOLNOPE');
	}

	public function testToStorage():void{
		$a = $this->storage->toStorage($this->token);
		$b = $this->storage->fromStorage($a);

		static::assertIsString($a);
		static::assertInstanceOf(AccessToken::class, $b);
		static::assertEquals($this->token, $b);
	}

	public function testFromStorageInvalidInputException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('invalid data');

		$this->storage->fromStorage([]);
	}

	public function testStoreWithExistingToken():void{
		$this->storage->storeAccessToken($this->tsn, $this->token);

		$this->token->extraParams = array_merge($this->token->extraParams, ['q' => 'u here?']);

		$this->storage->storeAccessToken($this->tsn, $this->token);

		$token = $this->storage->getAccessToken($this->tsn);

		static::assertSame('u here?', $token->extraParams['q']);
	}

}
