<?php
/**
 * Class BigCartelTest
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\BigCartel;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\BigCartel $provider
 */
class BigCartelTest extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return BigCartel::class;
	}

	protected function setUp():void{
		// modify test response data before loading into the test http client
		$this->testResponses['/oauth2/revoke_token'] = '';
		$this->testResponses['/oauth2/api/accounts'] = '{"data":[{"id":"12345"}]}';

		parent::setUp();
	}

}
