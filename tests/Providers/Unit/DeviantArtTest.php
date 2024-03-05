<?php
/**
 * Class DeviantArtTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\DeviantArt;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\DeviantArt $provider
 */
class DeviantArtTest extends OAuth2ProviderTestAbstract{

	protected string $FQN = DeviantArt::class;

	protected function setUp():void{
		// modify test response data before loading into the test http client
		$this->testResponses['/oauth2/revoke_token'] = '{"success": true}';

		parent::setUp();
	}

}
