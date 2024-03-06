<?php
/**
 * Class TwitterAPITest
 *
 * @created      11.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Providers\Twitter;
use chillerlan\OAuthTest\Providers\OAuth1APITestAbstract;

/**
 * Twitter API tests & examples
 *
 * @link https://developer.twitter.com/en/docs/api-reference-index
 *
 * @property \chillerlan\OAuth\Providers\Twitter $provider
 */
class TwitterAPITest extends OAuth1APITestAbstract{

	protected string $ENV = 'TWITTER';

	protected string $screen_name;
	protected int $user_id;

	protected function setUp():void{
		parent::setUp();

		$token             = $this->storage->getAccessToken($this->provider->serviceName);
		$this->screen_name = $token->extraParams['screen_name'];
	}

	protected function getProviderFQCN():string{
		return Twitter::class;
	}

	public function testMe():void{
		try{
			$this::assertSame($this->screen_name, MessageUtil::decodeJSON($this->provider->me())->screen_name);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
