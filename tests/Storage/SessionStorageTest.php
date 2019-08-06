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
	public function testTokenStorage(){
		parent::testTokenStorage();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testClearAllAccessTokens(){
		parent::testClearAllAccessTokens();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRetrieveCSRFStateNotFoundException(){
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('state not found');

		parent::testRetrieveCSRFStateNotFoundException();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRetrieveAccessTokenNotFoundException(){
		$this->expectException(OAuthStorageException::class);
		$this->expectExceptionMessage('token not found');

		parent::testRetrieveAccessTokenNotFoundException();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testToStorage(){
		parent::testToStorage();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testStoreWithExistingToken(){
		parent::testStoreWithExistingToken();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testStoreStateWithNonExistentArray(){
		$options = new OAuthOptions;
		unset($_SESSION[$options->sessionStateVar]);

		$this->assertFalse($this->storage->hasCSRFState($this->tsn));
		$this->storage->storeCSRFState($this->tsn, 'foobar');
		$this->assertTrue($this->storage->hasCSRFState($this->tsn));
	}
}
