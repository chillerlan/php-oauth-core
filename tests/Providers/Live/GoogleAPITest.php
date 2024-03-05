<?php
/**
 * Class GoogleAPITest
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Google;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * Google API usage tests/examples
 *
 * @link https://developers.google.com/oauthplayground/
 *
 * @property \chillerlan\OAuth\Providers\Google $provider
 */
class GoogleAPITest extends OAuth2APITestAbstract{

	protected string $FQN = Google::class;
	protected string $ENV = 'GOOGLE';

	public function testMe():void{
		$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->email);
	}

}
