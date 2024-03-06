<?php
/**
 * Class DummyOAuth2TestBasic
 *
 * @created      06.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * Tests a minimal OAuth2 provider implementation (no token refresh, no csrf, no nothing)
 *
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
final class DummyOAuth2TestBasic extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return DummyOAuth2BasicProvider::class;
	}

	public function testRefreshAccessTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token refresh not supported');

		$this->provider->refreshAccessToken();
	}

	public function testGetClientCredentialsTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('client credentials token not supported');

		$this->provider->getClientCredentialsToken();
	}

	public function testCheckStateNotSupportedException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('CSRF protection not supported');

		$this->provider->checkState();
	}

	public function testSetStateNotSupportedException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('CSRF protection not supported');

		$this->provider->setState([]);
	}

}
