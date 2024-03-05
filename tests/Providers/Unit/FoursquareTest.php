<?php
/**
 * Class FoursquareTest
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Foursquare;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Foursquare $provider
 */
class FoursquareTest extends OAuth2ProviderTestAbstract{

	protected string $FQN = Foursquare::class;

}
