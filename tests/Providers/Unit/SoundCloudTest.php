<?php
/**
 * Class SoundCloudTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\SoundCloud;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\SoundCloud $provider
 */
class SoundCloudTest extends OAuth2ProviderTestAbstract{

	protected string $FQN = SoundCloud::class;

}
