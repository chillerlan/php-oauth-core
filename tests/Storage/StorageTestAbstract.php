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

	protected function setUp(){
		$this->token = new AccessToken(['accessToken' => 'foobar']);
	}

	public function testTokenStorage(){

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

	public function testClearAllAccessTokens(){
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
	 */
	public function testRetrieveCSRFStateNotFoundException(){
		$this->storage->getCSRFState('LOLNOPE');
	}

	/**
	 * @expectedException \chillerlan\OAuth\Storage\OAuthStorageException
	 * @expectedExceptionMessage token not found
	 */
	public function testRetrieveAccessTokenNotFoundException(){
		$this->storage->getAccessToken('LOLNOPE');
	}

	public function testToStorage(){
		$a = $this->storage->toStorage($this->token);
		$b = $this->storage->fromStorage($a);

		$this->assertInternalType('string', $a);
		$this->assertInstanceOf(AccessToken::class, $b);
		$this->assertEquals($this->token, $b);
	}

}
