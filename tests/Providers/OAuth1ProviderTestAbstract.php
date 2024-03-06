<?php
/**
 * Class OAuth1ProviderTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{AccessToken, OAuth1Interface};
use chillerlan\OAuth\Providers\ProviderException;
use function parse_url;
use const PHP_URL_QUERY;

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
abstract class OAuth1ProviderTestAbstract extends OAuthProviderTestAbstract{

	protected array $testProperties = [
		'requestTokenURL' => 'https://localhost/oauth1/request_token',
		'accessTokenURL'  => 'https://localhost/oauth1/access_token',
		'revokeURL'       => 'https://localhost/oauth1/revoke_token',
		'apiURL'          => 'https://localhost/oauth1/api',
	];

	protected array $testResponses =  [
		'/oauth1/request_token' =>
			'oauth_token=test_request_token&oauth_token_secret=test_request_token_secret&oauth_callback_confirmed=true',
		'/oauth1/access_token'  =>
			'oauth_token=test_access_token&oauth_token_secret=test_access_token_secret&oauth_callback_confirmed=true',
		'/oauth1/revoke_token'  =>
			'{"message":"token revoked"}',
		'/oauth1/api/request'   =>
			'{"data":"such data! much wow!"}',
	];

	protected function setUp():void{
		parent::setUp();

		$this->provider->storeAccessToken(new AccessToken(['accessToken' => 'foo']));
	}

	public function testOAuth1Instance():void{
		$this::assertInstanceOf(OAuth1Interface::class, $this->provider);
	}

	public function testGetAuthURL():void{
		$query = QueryUtil::parse(parse_url((string)$this->provider->getAuthURL(), PHP_URL_QUERY));

		$this::assertSame('test_request_token', $query['oauth_token']);
	}

	public function testGetSignature():void{
		$signature = $this->reflection
			->getMethod('getSignature')
			->invokeArgs(
				$this->provider,
				['http://localhost/api/whatever', ['foo' => 'bar', 'oauth_signature' => 'should not see me!'], 'GET']
			);

		$this::assertSame('ygg22quLhpyegiyr7yl4hLAP9S8=', $signature);
	}

	public function testGetSignatureInvalidURLException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('getSignature: invalid url');

		$this->reflection
			->getMethod('getSignature')
			->invokeArgs($this->provider, ['whatever', [], 'GET']);
	}

	public function testGetAccessToken():void{
		$token = new AccessToken(['accessTokenSecret' => 'test_request_token_secret']);
		$this->provider->storeAccessToken($token);

		$token = $this->provider->getAccessToken('test_request_token', 'verifier');

		$this::assertSame('test_access_token', $token->accessToken);
		$this::assertSame('test_access_token_secret', $token->accessTokenSecret);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$this->responseFactory->createResponse(), false]);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		$body = $this->streamFactory->createStream('error=whatever');

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$this->responseFactory->createResponse()->withBody($body), false])
		;
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid token');

		$body = $this->streamFactory->createStream('oauth_token=whatever');

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$this->responseFactory->createResponse()->withBody($body), false])
		;
	}

	public function testParseTokenResponseCallbackUnconfirmedException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('oauth callback unconfirmed');

		$body = $this->streamFactory->createStream('oauth_token=whatever&oauth_token_secret=whatever_secret');

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$this->responseFactory->createResponse()->withBody($body), true])
		;
	}

	public function testGetRequestAuthorization():void{

		$authHeader = $this->provider
			->getRequestAuthorization(
				$this->requestFactory->createRequest('GET', 'https://foo.bar'),
				new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token'])
			)
			->getHeaderLine('Authorization');

		$this::assertStringContainsString('OAuth oauth_consumer_key="'.$this->options->key.'"', $authHeader);
		$this::assertStringContainsString('oauth_token="test_token"', $authHeader);
	}

	public function testRequest():void{
		$token = new AccessToken(['accessTokenSecret' => 'test_token']);
		$this->provider->storeAccessToken($token);

		$this::assertSame('such data! much wow!', MessageUtil::decodeJSON($this->provider->request('/request'))->data);

		// coverage, @todo
		$this->provider
			->request('/request', null, 'POST', ['foo' => 'bar'], ['Content-Type' => 'application/json']);
		$this->provider
			->request('/request', null, 'POST', ['foo' => 'bar'], ['Content-Type' => 'application/x-www-form-urlencoded']);
	}

}
