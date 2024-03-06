<?php
/**
 * Class DiscogsTest
 *
 * @created      05.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Discogs;
use chillerlan\OAuthTest\Providers\OAuth1ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Discogs $provider
 */
class DiscogsTest extends OAuth1ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return Discogs::class;
	}

}
