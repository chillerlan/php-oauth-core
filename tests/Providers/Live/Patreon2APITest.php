<?php
/**
 * Class Patreon2APITest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Core\ProviderException;
use chillerlan\OAuth\Providers\Patreon;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;
use function file_get_contents;

/**
 * @property \chillerlan\OAuth\Providers\Patreon $provider
 */
class Patreon2APITest extends OAuth2APITestAbstract{

	protected string $FQN = Patreon::class;
	protected string $ENV = 'PATREON2';

	protected function setUp():void{
		parent::setUp();
		$tokenfile = file_get_contents($this->CFG.'\\'.$this->provider->serviceName.'2.token.json');

		$this->storage->storeAccessToken((new AccessToken)->fromJSON($tokenfile), $this->provider->serviceName);
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
