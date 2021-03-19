<?php
/**
 * Class OAuth2APITestAbstract
 *
 * @created      08.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, OAuth2Interface};
use chillerlan\OAuth\Storage\MemoryStorage;

use function time;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2APITestAbstract extends OAuthAPITestAbstract{

	protected array $clientCredentialsScopes = [];

	public function testOAuth2Instance():void{
		$this::assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	public function testRequestCredentialsToken():void{

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
		}

		$this->provider->setStorage(new MemoryStorage);

		$token = $this->provider->getClientCredentialsToken($this->clientCredentialsScopes);

		$this::assertInstanceOf(AccessToken::class, $token);
		$this::assertIsString($token->accessToken);

		if($token->expires !== AccessToken::EOL_NEVER_EXPIRES){
			$this::assertGreaterThan(time(), $token->expires);
		}

		$this->logger->debug('OAuth2ClientCredentials', $token->toArray());
	}

}
