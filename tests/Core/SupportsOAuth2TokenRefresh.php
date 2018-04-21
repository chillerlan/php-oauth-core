<?php
/**
 * Trait SupportsOAuth2TokenRefresh
 *
 * @filesource   SupportsOAuth2TokenRefresh.php
 * @created      01.01.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\AccessToken;

trait SupportsOAuth2TokenRefresh{

	/**
	 * @expectedException \chillerlan\OAuth\OAuthException
	 * @expectedExceptionMessage no refresh token available, token expired [
	 */
	public function testRefreshAccessTokenNoRefreshTokenAvailable(){
		$this->storeToken(new AccessToken(['expires' => 1, 'refreshToken' => null]));

		$this->provider->refreshAccessToken();
	}

	public function testRefreshAccessToken(){
		$this->storeToken(new AccessToken(['expires' => 1, 'refreshToken' => 'test_refresh_token']));

		$token = $this->provider->refreshAccessToken();

		$this->assertSame('test_refresh_token', $token->refreshToken);
		$this->assertSame('test_refreshed_access_token', $token->accessToken);
		$this->assertGreaterThan(time(), $token->expires);
	}

	public function testRefreshAccessTokenBody(){
		$body = $this
			->getMethod('refreshAccessTokenBody')
			->invokeArgs($this->provider, ['whatever']);

		$this->assertSame('whatever', $body['refresh_token']);
		$this->assertSame($this->options->key, $body['client_id']);
		$this->assertSame($this->options->secret, $body['client_secret']);
		$this->assertSame('refresh_token', $body['grant_type']);
	}

	public function testRequestWithTokenRefresh(){
		$this->storeToken(new AccessToken(['accessToken' => 'test_access_token', 'refreshToken' => 'test_refresh_token', 'expires' => 1]));

		sleep(2);

		$this->assertSame('such data! much wow!', $this->provider->request('')->json->data);
	}

}
