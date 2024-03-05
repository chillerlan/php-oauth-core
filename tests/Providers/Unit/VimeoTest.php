<?php
/**
 * Class VimeoTest
 *
 * @created      09.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Vimeo;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Vimeo $provider
 */
class VimeoTest extends OAuth2ProviderTestAbstract{

	protected string $FQN = Vimeo::class;

	protected function setUp():void{
		// modify test response data before loading into the test http client
		$this->testResponses['/oauth2/revoke_token'] = '';

		parent::setUp();
	}

}
