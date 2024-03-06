<?php
/**
 * Class LastFMAPITest
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\LastFM;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuthAPITestAbstract;

/**
 * last.fm API test & examples
 *
 * @link https://www.last.fm/api/intro
 *
 * @property \chillerlan\OAuth\Providers\LastFM $provider
 */
class LastFMAPITest extends OAuthAPITestAbstract{

	protected string $ENV = 'LASTFM';

	protected function setUp():void{
		parent::setUp();

		// username is stored in the session token
		$token          = $this->storage->getAccessToken($this->provider->serviceName);
		$this->testuser = $token->extraParams['session']['name'];
	}

	protected function getProviderFQCN():string{
		return LastFM::class;
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->user->name);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
