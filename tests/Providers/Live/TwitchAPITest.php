<?php
/**
 * Class TwitchTest
 *
 * @created      15.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Providers\Twitch;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property  \chillerlan\OAuth\Providers\Twitch $provider
 */
class TwitchAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'TWITCH';

	protected function getProviderFQCN():string{
		return Twitch::class;
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->data[0]->display_name);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
