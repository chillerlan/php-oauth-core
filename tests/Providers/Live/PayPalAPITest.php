<?php
/**
 * Class PayPalAPITest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\ProviderException;
use chillerlan\OAuth\Providers\PayPal;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;
use function is_array;

/**
 * @property \chillerlan\OAuth\Providers\PayPal $provider
 */
class PayPalAPITest extends OAuth2APITestAbstract{

	protected string $FQN = PayPal::class;
	protected string $ENV = 'PAYPAL'; // PAYPAL_SANDBOX

	public function testMe():void{
		try{
			$json = MessageUtil::decodeJSON($this->provider->me());
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}

		if(!isset($json->emails) || !is_array($json->emails) || empty($json->emails)){
			$this->markTestSkipped('no email found');
		}

		foreach($json->emails as $email){
			if($email->primary){
				$this::assertSame($this->testuser, $email->value);
				return;
			}
		}
	}

}
