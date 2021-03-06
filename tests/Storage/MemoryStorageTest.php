<?php
/**
 * Class MemoryStorageTest
 *
 * @created      08.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Storage;

use chillerlan\OAuth\Storage\MemoryStorage;

class MemoryStorageTest extends StorageTestAbstract{

	protected function setUp():void{
		parent::setUp();

		$this->storage = new MemoryStorage;
	}

}
