<?php
/**
 * Class MixcloudAPITest
 *
 * @created      20.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Mixcloud;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Mixcloud $provider
 */
class MixcloudAPITest extends OAuth2APITestAbstract{

	protected string $FQN = Mixcloud::class;
	protected string $ENV = 'MIXCLOUD';

	public function testMe():void{
		$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->username);
	}

}
