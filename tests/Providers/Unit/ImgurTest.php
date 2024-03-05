<?php
/**
 * Class ImgurTest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Imgur;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Imgur $provider
 */
class ImgurTest extends OAuth2ProviderTestAbstract{

	protected string $FQN = Imgur::class;

}
