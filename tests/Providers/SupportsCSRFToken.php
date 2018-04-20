<?php
/**
 * Trait SupportsCSRFToken
 *
 * @filesource   SupportsCSRFToken.php
 * @created      20.04.2018
 * @package      chillerlan\OAuthTest\Providers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Providers\OAuth2Interface;

/**
 */
trait SupportsCSRFToken{

	public function testCheckCSRFState(){

		$provider = $this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['test_state']);

		$this->assertInstanceOf(OAuth2Interface::class, $provider);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage invalid state
	 */
	public function testCheckStateInvalid(){

		$this
			->getMethod('checkState')
			->invoke($this->provider);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage invalid CSRF state
	 */
	public function testCheckStateInvalidCSRFState(){

		$this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['invalid_test_state']);
	}


}
