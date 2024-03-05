<?php
/**
 * Class YouTubeTest
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\YouTube;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\YouTube $provider
 */
class YouTubeTest extends OAuth2ProviderTestAbstract{

	protected string $FQN = YouTube::class;

}
