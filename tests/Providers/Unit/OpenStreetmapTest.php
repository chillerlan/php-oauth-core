<?php
/**
 * Class OpenStreetmapTest
 *
 * @created      12.05.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\OpenStreetmap;
use chillerlan\OAuthTest\Providers\OAuth1ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap $provider
 */
class OpenStreetmapTest extends OAuth1ProviderTestAbstract{

	protected string $FQN = OpenStreetmap::class;

}
