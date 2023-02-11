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

use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};

class MemoryStorageTest extends StorageTestAbstract{

	protected function initStorage():OAuthStorageInterface{
		return new MemoryStorage;
	}

}
