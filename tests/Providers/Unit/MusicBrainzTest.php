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

/**
 * @property \chillerlan\OAuth\Providers\MusicBrainz $provider
 */
final class MusicBrainzTest extends OAuth2ProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return MusicBrainz::class;
	}

}
