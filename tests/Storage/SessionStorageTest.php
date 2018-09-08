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

use chillerlan\OAuth\Storage\SessionStorage;

/**
 * @runInSeparateProcess
 */
class SessionStorageTest extends StorageTestAbstract{

	protected function setUp(){
		parent::setUp();

		$this->storage = new SessionStorage;
	}

}
