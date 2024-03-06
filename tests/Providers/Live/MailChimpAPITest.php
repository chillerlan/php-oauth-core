<?php
/**
 * Class MailChimpAPITest
 *
 * @created      16.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\MailChimp;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * MailChimp API usage tests/examples
 *
 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/overview/
 *
 * @property \chillerlan\OAuth\Providers\MailChimp $provider
 */
class MailChimpAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'MAILCHIMP';

	protected function getProviderFQCN():string{
		return MailChimp::class;
	}

	public function testGetTokenMetadata():void{
		$token = $this->storage->getAccessToken($this->provider->serviceName);
		$token = $this->provider->getTokenMetadata($token);

		$this::assertSame($this->testuser, $token->extraParams['accountname']);
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->account_name);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
