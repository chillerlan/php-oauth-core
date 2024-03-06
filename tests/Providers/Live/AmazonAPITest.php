<?php
/**
 * Class AmazonAPITest
 *
 * @created      10.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Amazon;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * Amazon API usage tests/examples
  *
 * @property \chillerlan\OAuth\Providers\Amazon $provider
 */
class AmazonAPITest extends OAuth2APITestAbstract{

	protected string $FQN = Amazon::class;
	protected string $ENV = 'AMAZON';

	public function testMe():void{
		try{
			$this::assertMatchesRegularExpression('/[a-z\d.]+/i', MessageUtil::decodeJSON($this->provider->me())->user_id);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
