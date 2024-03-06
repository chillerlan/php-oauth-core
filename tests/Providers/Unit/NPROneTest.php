<?php
/**
 * Class NPROneTest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\OAuthException;
use chillerlan\OAuth\Providers\NPROne;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @property \chillerlan\OAuth\Providers\NPROne $provider
 */
class NPROneTest extends OAuth2ProviderTestAbstract{

	protected const TEST_PROPERTIES = [
		'apiURL'                    => '/oauth2/api',
		'accessTokenURL'            => '/oauth2/access_token',
		'refreshTokenURL'           => '/oauth2/refresh_token',
		'clientCredentialsTokenURL' => '/oauth2/client_credentials',
		'revokeURL'                 => '/revoke_token',
	];

	protected const TEST_RESPONSES = [
		'/oauth2/access_token'       =>
			'{"access_token":"test_access_token","expires_in":3600,"state":"test_state","scope":"some_scope other_scope"}',
		'/oauth2/refresh_token'      =>
			'{"access_token":"test_refreshed_access_token","expires_in":60,"state":"test_state"}',
		'/oauth2/api/revoke_token'   =>
			'{"message":"token revoked"}',
		'/oauth2/client_credentials' =>
			'{"access_token":"test_client_credentials_token","expires_in":30,"state":"test_state"}',
		'/oauth2/api/request'        =>
			'{"data":"such data! much wow!"}',
	];

	protected function getProviderFQCN():string{
		return NPROne::class;
	}

	public function testRequestInvalidAuthTypeException():void{
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('invalid auth type');

		$this->reflection->getProperty('authMethod')->setValue($this->provider, -1);

		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->storage->storeAccessToken($token, $this->provider->serviceName);

		$this->provider->request('https://foo.api.npr.org/');
	}

	public static function requestTargetProvider():array{
		return [
			'empty'          => ['', 'https://localhost/api'],
			'slash'          => ['/', 'https://localhost/api/'],
			'no slashes'     => ['a', 'https://localhost/api/a'],
			'leading slash'  => ['/b', 'https://localhost/api/b'],
			'trailing slash' => ['c/', 'https://localhost/api/c/'],
#			'full url given' => ['https://localhost/other/path/d', 'https://localhost/other/path/d'],
#			'ignore params'  => ['https://localhost/api/e/?with=param#foo', 'https://localhost/api/e/'],
		];
	}

	public function testSetAPI():void{
		$this->provider = $this->initProvider($this->getProviderFQCN());

		$this::assertSame('https://listening.api.npr.org', $this->reflection->getProperty('apiURL')->getValue($this->provider));

		$this->provider->setAPI('station');

		$this::assertSame('https://station.api.npr.org', $this->reflection->getProperty('apiURL')->getValue($this->provider));
	}

}
