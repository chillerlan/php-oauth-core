<?php
/**
 * Class MusicBrainzTest
 *
 * @created      31.07.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\MusicBrainz;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\MusicBrainz $provider
 */
class MusicBrainzTest extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return MusicBrainz::class;
	}

}
