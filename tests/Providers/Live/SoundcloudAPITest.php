<?php
/**
 * Class SoundcloudAPITest
 *
 * @created      16.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Providers\SoundCloud;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property  \chillerlan\OAuth\Providers\SoundCloud $provider
 */
class SoundcloudAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'SOUNDCLOUD';

	protected function getProviderFQCN():string{
		return SoundCloud::class;
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->username);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

	public function testRequestCredentialsToken():void{
		$this::markTestSkipped('may fail because SoundCloud deleted older applications');
	}

}
