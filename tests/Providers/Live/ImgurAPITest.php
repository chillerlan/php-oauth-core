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
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Imgur $provider
 */
class ImgurAPITest extends OAuth2APITestAbstract{

	protected string $FQN = Imgur::class;
	protected string $ENV = 'IMGUR';

	protected function setUp():void{
		parent::setUp();

		$token = $this->storage->getAccessToken($this->provider->serviceName);

		$this->testuser = $token->extraParams['account_id'];
	}

	public function testMe():void{
		$this::assertSame((int)$this->testuser, MessageUtil::decodeJSON($this->provider->me())->data->id);
	}

}
