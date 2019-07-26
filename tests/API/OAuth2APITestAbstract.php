<?php
/**
 * Class OAuth2APITestAbstract
 *
 * @filesource   OAuth2APITestAbstract.php
 * @created      08.09.2018
 * @package      chillerlan\OAuthTest\API
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, OAuth2Interface};

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2APITestAbstract extends APITestAbstract{

	public function testOAuth2Instance(){
		$this->assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	public function testRequestCredentialsToken(){

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');

			return;
		}

		$token = $this->provider->getClientCredentialsToken();

		$this->assertInstanceOf(AccessToken::class, $token);
		$this->assertIsString($token->accessToken);

		if($token->expires !== AccessToken::EOL_NEVER_EXPIRES){
			$this->assertGreaterThan(\time(), $token->expires);
		}

		$this->logger->debug('OAuth2ClientCredentials', $token->toArray());
	}

}
