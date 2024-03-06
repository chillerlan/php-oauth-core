<?php
/**
 * Class WordpressAPITest
 *
 * @created      21.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Providers\WordPress;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\WordPress $provider
 */
class WordpressAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'WORDPRESS';

	protected function getProviderFQCN():string{
		return WordPress::class;
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
