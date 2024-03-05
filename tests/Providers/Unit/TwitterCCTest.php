<?php
/**
 * Class TwitterCCTest
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\ProviderException;
use chillerlan\OAuth\Providers\TwitterCC;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\TwitterCC $provider
 */
class TwitterCCTest extends OAuth2ProviderTestAbstract{

	protected string $FQN = TwitterCC::class;

	public function testGetAuthURL():void{
		$this->markTestSkipped('N/A');
	}

	public function testGetAccessToken():void{
		$this->markTestSkipped('N/A');
	}

	public function testRequestGetAuthURLNotSupportedException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('TwitterCC only supports Client Credentials Grant');

		$this->provider->getAuthURL();
	}

	public function testRequestGetAccessTokenNotSupportedException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('TwitterCC only supports Client Credentials Grant');

		$this->provider->getAccessToken('foo');
	}

}
