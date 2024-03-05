<?php
/**
 * Class OpenStreetmapAPITest
 *
 * @created      12.05.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\ProviderException;
use chillerlan\OAuth\Providers\OpenStreetmap;
use chillerlan\OAuthTest\Providers\OAuth1APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap $provider
 */
class OpenStreetmapAPITest extends OAuth1APITestAbstract{

	protected string $FQN = OpenStreetmap::class;
	protected string $ENV = 'OPENSTREETMAP';

	public function testMe():void{
		try{
			// json
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->user->display_name);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
