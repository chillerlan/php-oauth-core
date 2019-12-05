<?php
/**
 * Class SessionStorageTest
 *
 * @filesource   SessionStorageTest.php
 * @created      08.09.2018
 * @package      chillerlan\OAuthTest\Storage
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\{OAuthStorageException, SessionStorage};

/**
 * @runInSeparateProcess
 */
class SessionStorageTest extends StorageTestAbstract{

	protected function setUp():void{
		parent::setUp();

		$this->storage = new SessionStorage;
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testTokenStorage():void{
		parent::testTokenStorage();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testClearAllAccessTokens():void{
		parent::testClearAllAccessTokens();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRetrieveCSRFStateNotFoundException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('state not found');

		parent::testRetrieveCSRFStateNotFoundException();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRetrieveAccessTokenNotFoundException():void{
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('token not found');

		parent::testRetrieveAccessTokenNotFoundException();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testToStorage():void{
		parent::testToStorage();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testStoreWithExistingToken():void{
		parent::testStoreWithExistingToken();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testFromStorageInvalidInputException():void{
		parent::testFromStorageInvalidInputException();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testStoreStateWithNonExistentArray():void{
		$options = new OAuthOptions;
		unset($_SESSION[$options->sessionStateVar]);

		static::assertFalse($this->storage->hasCSRFState($this->tsn));
		$this->storage->storeCSRFState($this->tsn, 'foobar');
		static::assertTrue($this->storage->hasCSRFState($this->tsn));
	}
}
