<?php
/**
 * Class OAuth1APITestAbstract
 *
 * @filesource   OAuth1APITestAbstract.php
 * @created      09.04.2018
 * @package      chillerlan\OAuthTest\API
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\OAuth\Providers\OAuth1Interface;

abstract class OAuth1APITestAbstract extends APITestAbstract{

	public function testOAuth1Instance(){
		$this->assertInstanceOf(OAuth1Interface::class, $this->provider);
	}

}
