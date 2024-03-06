<?php
/**
 * Class SpotifyAPITest
 *
 * @created      06.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Providers\Spotify;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * Spotify API usage tests/examples
 *
 * Please note that Spotify ids may change and so these test may fail at times
 *
 * @link https://developer.spotify.com/web-api/endpoint-reference/
 *
 * @property \chillerlan\OAuth\Providers\Spotify $provider
 */
class SpotifyAPITest extends OAuth2APITestAbstract{

	protected string $FQN = Spotify::class;
	protected string $ENV = 'SPOTIFY';


	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->id);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
