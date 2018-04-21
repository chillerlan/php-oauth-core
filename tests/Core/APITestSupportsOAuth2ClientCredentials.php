<?php
/**
 * Trait APITestSupportsOAuth2ClientCredentials
 *
 * @filesource   APITestSupportsOAuth2ClientCredentials.php
 * @created      20.04.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\AccessToken;

trait APITestSupportsOAuth2ClientCredentials{

	public function testRequestCredentialsToken(){

		$token = $this->provider->getClientCredentialsToken();

		$this->assertInstanceOf(AccessToken::class, $token);
		$this->assertInternalType('string', $token->accessToken);

		if($token->expires !== AccessToken::EOL_NEVER_EXPIRES){
			$this->assertGreaterThan(time(), $token->expires);
		}

		$this->logger->debug('APITestSupportsOAuth2ClientCredentials', $token->__toArray());
	}

}
