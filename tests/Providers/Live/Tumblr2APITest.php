<?php
/**
 * Class Tumblr2APITest
 *
 * @created      30.07.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Tumblr2;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;
use Throwable;

/**
 * @property \chillerlan\OAuth\Providers\Tumblr2 $provider
 */
class Tumblr2APITest extends OAuth2APITestAbstract{

	protected string $FQN = Tumblr2::class;
	protected string $ENV = 'TUMBLR';

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->response->user->name);
		}
		catch(Throwable){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
