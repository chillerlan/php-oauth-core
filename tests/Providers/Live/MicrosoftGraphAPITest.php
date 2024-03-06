<?php
/**
 * Class MicrosoftGraphAPITest
 *
 * @created      30.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\MicrosoftGraph;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\MicrosoftGraph $provider
 */
class MicrosoftGraphAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'MICROSOFT_AAD';

	protected function getProviderFQCN():string{
		return MicrosoftGraph::class;
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->userPrincipalName);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
