<?php
/**
 * Class GitHubAPITest
 *
 * @created      18.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\GitHub;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property  \chillerlan\OAuth\Providers\GitHub $provider
 */
class GitHubAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'GITHUB';

	protected function getProviderFQCN():string{
		return GitHub::class;
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->login);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
