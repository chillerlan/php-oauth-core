<?php
/**
 * Class DummyOAuth1Test
 *
 * @created      09.09.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\OAuth1;

use chillerlan\OAuthTest\Providers\OAuth1ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
final class DummyOAuth1Test extends OAuth1ProviderTestAbstract{

	protected string $FQN = DummyOAuth1Provider::class;

}
