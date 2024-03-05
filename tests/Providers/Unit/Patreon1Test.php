<?php
/**
 * Class Patreon1Test
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Patreon;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Patreon $provider
 */
class Patreon1Test extends OAuth2ProviderTestAbstract{

	protected string $FQN = Patreon::class;

}
