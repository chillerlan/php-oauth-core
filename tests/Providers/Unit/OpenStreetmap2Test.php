<?php
/**
 * Class OpenStreetmapTest
 *
 * @created      05.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\OpenStreetmap2;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap $provider
 */
class OpenStreetmap2Test extends OAuth2ProviderTestAbstract{

	protected string $FQN = OpenStreetmap2::class;

}
