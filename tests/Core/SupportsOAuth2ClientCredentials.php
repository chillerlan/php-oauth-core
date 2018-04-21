<?php
/**
 * Trait SupportsOAuth2ClientCredentials
 *
 * @filesource   SupportsOAuth2ClientCredentials.php
 * @created      01.01.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\ClientCredentials;

trait SupportsOAuth2ClientCredentials{

	public function testClientCredentialsInterface(){
		$this->assertInstanceOf(ClientCredentials::class, $this->provider);
	}

	public function testGetClientCredentialsTokenBody(){
		$this->setProperty($this->provider, 'scopesDelimiter', ',');

		$body = $this
			->getMethod('getClientCredentialsTokenBody')
			->invokeArgs($this->provider, [['scope1', 'scope2', 'scope3']]);

		$this->assertSame('scope1,scope2,scope3', $body['scope']);
		$this->assertSame('client_credentials', $body['grant_type']);
	}

	public function testGetClientCredentialsHeaders(){
		$headers = $this
			->getMethod('getClientCredentialsTokenHeaders')
			->invoke($this->provider);

		$this->assertSame('Basic dGVzdGtleTp0ZXN0c2VjcmV0', $headers['Authorization']);
	}

	public function testGetClientCredentials(){
		$token = $this->provider->getClientCredentialsToken();

		$this->assertSame('test_client_credentials_token', $token->accessToken);
		$this->assertGreaterThan(time(), $token->expires);
	}


}
