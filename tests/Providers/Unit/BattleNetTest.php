<?php
/**
 * Class BattleNetTest
 *
 * @created      02.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\BattleNet;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\BattleNet $provider
 */
class BattleNetTest extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return BattleNet::class;
	}

	public function testSetRegion():void{
		$this->provider->setRegion('cn');
		$this::assertSame('https://gateway.battlenet.com.cn', $this->provider->apiURL);

		$this->provider->setRegion('us');
		$this::assertSame('https://us.api.blizzard.com', $this->provider->apiURL);
	}

	public function testSetRegionException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid region: foo');

		$this->provider->setRegion('foo');
	}

}
