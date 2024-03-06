<?php
/**
 * Class TwitchTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Twitch;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Twitch $provider
 */
class TwitchTest extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return Twitch::class;
	}

	public function testRequestInvalidAuthTypeException():void{
		$this::markTestSkipped('N/A');
	}

}
