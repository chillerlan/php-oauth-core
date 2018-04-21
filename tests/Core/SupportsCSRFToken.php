<?php
/**
 * Trait SupportsCSRFToken
 *
 * @filesource   SupportsCSRFToken.php
 * @created      20.04.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\OAuth2Interface;

trait SupportsCSRFToken{

	public function testCheckCSRFState(){

		$provider = $this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['test_state']);

		$this->assertInstanceOf(OAuth2Interface::class, $provider);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage invalid state
	 */
	public function testCheckStateInvalid(){

		$this
			->getMethod('checkState')
			->invoke($this->provider);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage invalid CSRF state
	 */
	public function testCheckStateInvalidCSRFState(){

		$this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['invalid_test_state']);
	}


}
