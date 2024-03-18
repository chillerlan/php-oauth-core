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

/**
 * @property \chillerlan\OAuth\Providers\Twitch $provider
 */
class TwitchTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Twitch::class;
	}

}
