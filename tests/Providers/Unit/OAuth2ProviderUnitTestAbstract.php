<?php
/**
 * Class OAuth2ProviderUnitTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, CSRFToken, OAuth2Interface, OAuthInterface, TokenRefresh};
use chillerlan\OAuth\OAuthException;
use chillerlan\OAuth\Providers\ProviderException;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2ProviderUnitTestAbstract extends OAuthProviderUnitTestAbstract{

	protected OAuthInterface|OAuth2Interface $provider;

	public function testOAuth2Instance():void{
		$this::assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	/*
	 * request authorization
	 */

	public function testGetRequestAuthorization():void{
		$request    = $this->requestFactory->createRequest('GET', 'https://foo.bar');
		$token      = new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token']);
		$authMethod = $this->provider::AUTH_METHOD;

		// header (default)
		if($authMethod === OAuth2Interface::AUTH_METHOD_HEADER){
			$this::assertStringContainsString(
				$this->provider::AUTH_PREFIX_HEADER.' test_token',
				$this->provider->getRequestAuthorization($request, $token)->getHeaderLine('Authorization')
			);
		}
		// query
		elseif($authMethod === OAuth2Interface::AUTH_METHOD_QUERY){
			$this::assertStringContainsString(
				$this->provider::AUTH_PREFIX_QUERY.'=test_token',
				$this->provider->getRequestAuthorization($request, $token)->getUri()->getQuery()
			);
		}

	}

	/*
	 * CSRF state
	 */

	public function testCheckCSRFState():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->storage->storeCSRFState('test_state', $this->provider->serviceName);

		$this::assertTrue($this->storage->hasCSRFState($this->provider->serviceName));

		// will delete the state after a successful check
		$this->invokeReflectionMethod('checkState', ['test_state']);

		$this::assertFalse($this->storage->hasCSRFState($this->provider->serviceName));
	}

	public function testCheckCSRFStateEmptyException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid CSRF state');

		$this->invokeReflectionMethod('checkState');
	}

	public function testCheckCSRFStateInvalidStateException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid CSRF state');

		$this->invokeReflectionMethod('checkState', ['invalid_test_state']);
	}

	/*
	 * token refresh
	 */

	public function testRefreshAccessTokenNoRefreshTokenAvailable():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('no refresh token available, token expired [');

		$token = new AccessToken(['expires' => 1, 'refreshToken' => null]);
		$this->provider->storeAccessToken($token);

		$this->provider->refreshAccessToken();
	}

}
