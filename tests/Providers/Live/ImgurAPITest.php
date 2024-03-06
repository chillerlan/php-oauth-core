<?php
/**
 * Class ImgurAPITest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Imgur;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Imgur $provider
 */
class ImgurAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'IMGUR';

	protected function setUp():void{
		parent::setUp();

		$token = $this->storage->getAccessToken($this->provider->serviceName);

		$this->testuser = $token->extraParams['account_id'];
	}

	protected function getProviderFQCN():string{
		return Imgur::class;
	}

	public function testMe():void{
		try{
			$this::assertSame((int)$this->testuser, MessageUtil::decodeJSON($this->provider->me())->data->id);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
