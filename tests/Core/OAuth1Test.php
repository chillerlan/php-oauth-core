<?php
/**
 * Class OAuth1Test
 *
 * @created      09.09.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuthTest\Providers\OAuth1ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
final class OAuth1Test extends OAuth1ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return DummyOAuth1Provider::class;
	}

}
