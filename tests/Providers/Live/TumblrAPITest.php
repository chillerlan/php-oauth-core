<?php
/**
 * Class TumblrTest
 *
 * @created      24.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Providers\Tumblr;
use chillerlan\OAuthTest\Providers\OAuth1APITestAbstract;

/**
 * @property  \chillerlan\OAuth\Providers\Tumblr $provider
 */
class TumblrAPITest extends OAuth1APITestAbstract{

	protected string $FQN = Tumblr::class;
	protected string $ENV = 'TUMBLR';

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->response->user->name);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

	public function testTokenExchange():void{
		// only outcomment if wou want to deliberately invaildate your current token
		$this::markTestSkipped('N/A - will invalidate the current token');

#		$this::assertSame('bearer', $this->provider->exchangeForOAuth2Token()->extraParams['token_type']);
	}

}
