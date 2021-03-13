<?php
/**
 * Class OAuth1APITestAbstract
 *
 * @created      08.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\OAuth\Core\OAuth1Interface;

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
abstract class OAuth1APITestAbstract extends APITestAbstract{

	public function testOAuth1Instance():void{
		static::assertInstanceOf(OAuth1Interface::class, $this->provider);
	}

}
