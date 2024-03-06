<?php
/**
 * Class Tumblr2Test
 *
 * @created      30.07.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Tumblr2;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Tumblr2 $provider
 */
class Tumblr2Test extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return Tumblr2::class;
	}

}
