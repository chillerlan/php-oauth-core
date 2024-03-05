<?php
/**
 * Class DeviantArtAPITest
 *
 * @created      27.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\ProviderException;
use chillerlan\OAuth\Providers\DeviantArt;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\DeviantArt $provider
 */
class DeviantArtAPITest extends OAuth2APITestAbstract{

	protected string $FQN = DeviantArt::class;
	protected string $ENV = 'DEVIANTART';

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->username);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
