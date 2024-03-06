<?php
/**
 * Class Patreon1APITest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\Patreon;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;
use function file_get_contents;

/**
 * @property \chillerlan\OAuth\Providers\Patreon $provider
 */
class Patreon1APITest extends OAuth2APITestAbstract{

	protected string $ENV = 'PATREON1';

	protected function setUp():void{
		parent::setUp();
		$tokenfile = file_get_contents($this->CFG.'\\'.$this->provider->serviceName.'1.token.json');

		$this->storage->storeAccessToken((new AccessToken)->fromJSON($tokenfile), $this->provider->serviceName);
	}

	protected function getProviderFQCN():string{
		return Patreon::class;
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->data->attributes->email);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
