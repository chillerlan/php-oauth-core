<?php
/**
 * Class OAuth1ProviderUnitTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, OAuth1Interface, OAuthInterface};
use chillerlan\OAuth\Providers\ProviderException;

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
abstract class OAuth1ProviderUnitTestAbstract extends OAuthProviderUnitTestAbstract{

	protected OAuthInterface|OAuth1Interface $provider;

	public function testOAuth1Instance():void{
		$this::assertInstanceOf(OAuth1Interface::class, $this->provider);
	}

	/*
	 * request authorization
	 */

	public function testGetRequestAuthorization():void{
		$request = $this->requestFactory->createRequest('GET', 'https://foo.bar');
		$token   = new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token']);

		$authHeader = $this->provider
			->getRequestAuthorization($request, $token)
			->getHeaderLine('Authorization')
		;

		$this::assertStringContainsString('OAuth oauth_consumer_key="'.$this->options->key.'"', $authHeader);
		$this::assertStringContainsString('oauth_token="test_token"', $authHeader);
	}

	/*
	 * signature
	 */

	public function testGetSignature():void{
		$expected = 'fvkt6r6LhR0TgMvDOGsSlzB7IR4=';

		$signature = $this->invokeReflectionMethod(
			'getSignature',
			['https://localhost/api/whatever', ['foo' => 'bar', 'oauth_signature' => 'should not see me!'], 'GET'],
		);

		$this::assertSame($expected, $signature);

		// the "oauth_signature" parameter should be unset if present
		$signature = $this->invokeReflectionMethod(
			'getSignature',
			['https://localhost/api/whatever', ['foo' => 'bar'], 'GET'],
		);

		$this::assertSame($expected, $signature);
	}

	public function testGetSignatureInvalidURLException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('getSignature: invalid url');

		$this->invokeReflectionMethod('getSignature', ['http://localhost/boo', [], 'GET']);
	}


}
