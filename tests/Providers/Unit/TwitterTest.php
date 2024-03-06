<?php
/**
 * Class TwitterTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Twitter;
use chillerlan\OAuthTest\Providers\OAuth1ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Twitter $provider
 */
class TwitterTest extends OAuth1ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return Twitter::class;
	}

}
