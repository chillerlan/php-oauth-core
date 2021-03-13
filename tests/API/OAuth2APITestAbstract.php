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
abstract class OAuth2APITestAbstract extends APITestAbstract{

	protected array $clientCredentialsScopes = [];

	public function testOAuth2Instance():void{
		static::assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	public function testRequestCredentialsToken():void{

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');

			return;
		}

		$this->provider->setStorage(new MemoryStorage);

		$token = $this->provider->getClientCredentialsToken($this->clientCredentialsScopes);

		static::assertInstanceOf(AccessToken::class, $token);
		static::assertIsString($token->accessToken);

		if($token->expires !== AccessToken::EOL_NEVER_EXPIRES){
			static::assertGreaterThan(time(), $token->expires);
		}

		$this->logger->debug('OAuth2ClientCredentials', $token->toArray());
	}

}
