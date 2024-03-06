<?php
/**
 * Class MastodonAPITest
 *
 * @created      19.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Mastodon;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * Spotify API usage tests/examples
 *
 * @link https://github.com/tootsuite/documentation/blob/master/Using-the-API/API.md
 *
 * @property \chillerlan\OAuth\Providers\Mastodon $provider
 */
class MastodonAPITest extends OAuth2APITestAbstract{

	protected string $FQN = Mastodon::class;
	protected string $ENV = 'MASTODON';

	protected string $testInstance;

	protected function setUp():void{
		parent::setUp();

		$this->testInstance = ($this->dotEnv->get($this->ENV.'_INSTANCE') ?? '');

		$this->provider->setInstance($this->testInstance);
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->acct);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
