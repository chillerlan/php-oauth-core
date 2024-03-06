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

	protected string $FQN = NPROne::class;

	protected function setUp():void{
		// modify test response data before loading into the test http client
		// using the api url exit of NPROne::getRequestTarget() because reasons
		$this->testResponses['/oauth2/api/revoke_token'] = '{"message":"token revoked"}';

		parent::setUp();

		$this->reflection->getProperty('revokeURL')->setValue($this->provider, '/revoke_token');

	}

	public function testRequestInvalidAuthTypeException():void{
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('invalid auth type');

		$this->reflection->getProperty('authMethod')->setValue($this->provider, -1);

		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->storage->storeAccessToken($token, $this->provider->serviceName);

		$this->provider->request('https://foo.api.npr.org/');
	}

	#[DataProvider('requestTargetProvider')]
	public function testGetRequestTarget(string $path, string $expected):void{
		$this::markTestSkipped('N/A');
	}

	public function testSetAPI():void{
		$this->provider = $this->initProvider($this->FQN);

		$this::assertSame('https://listening.api.npr.org', $this->reflection->getProperty('apiURL')->getValue($this->provider));

		$this->provider->setAPI('station');

		$this::assertSame('https://station.api.npr.org', $this->reflection->getProperty('apiURL')->getValue($this->provider));
	}

}
