<?php
/**
 * Class DeezerTest
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Deezer;

/**
 * @property \chillerlan\OAuth\Providers\Deezer $provider
 */
class DeezerTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return Deezer::class;
	}

}
