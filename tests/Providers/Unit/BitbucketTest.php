<?php
/**
 * Class BitbucketTest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Bitbucket;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Bitbucket $provider
 */
class BitbucketTest extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return Bitbucket::class;
	}

}
