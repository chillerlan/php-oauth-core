<?php
/**
 * Class BigCartelAPITest
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\ProviderException;
use chillerlan\OAuth\Providers\BigCartel;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\BigCartel $provider
 */
class BigCartelAPITest extends OAuth2APITestAbstract{

	protected string $FQN = BigCartel::class;
	protected string $ENV = 'BIGCARTEL';

	protected int $account_id;

	protected function setUp():void{
		parent::setUp();

		$this->account_id = (int)$this->storage->getAccessToken($this->provider->serviceName)->extraParams['account_id'];
	}

	public function testMe():void{
		try{
			$this::assertSame($this->account_id, (int)MessageUtil::decodeJSON($this->provider->me())->data[0]->id);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
