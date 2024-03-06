<?php
/**
 * Class DiscogsAPITest
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Discogs;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth1APITestAbstract;

/**
 * Discogs API test
 *
 * @property \chillerlan\OAuth\Providers\Discogs $provider
 */
class DiscogsAPITest extends OAuth1APITestAbstract{

	protected string $ENV = 'DISCOGS';

	protected function getProviderFQCN():string{
		return Discogs::class;
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->username);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
