<?php
/**
 * Class OAuth1ProviderTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\{AccessToken, OAuth1Interface, ProviderException};

use function chillerlan\HTTP\Psr17\create_stream_from_input;
use function chillerlan\HTTP\Psr7\get_json;

use function parse_str, parse_url;

use const PHP_URL_QUERY;

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
abstract class OAuth1ProviderTestAbstract extends OAuthProviderTestAbstract{

	protected array $testResponses =  [
		'/oauth1/request_token' =>
			'oauth_token=test_request_token&oauth_token_secret=test_request_token_secret&oauth_callback_confirmed=true',
		'/oauth1/access_token'  =>
			'oauth_token=test_access_token&oauth_token_secret=test_access_token_secret&oauth_callback_confirmed=true',
		'/oauth1/api/request'   =>
			'{"data":"such data! much wow!"}',
	];

	protected function setUp():void{
		parent::setUp();

		$this->setProperty($this->provider, 'requestTokenURL', 'https://localhost/oauth1/request_token');
		$this->setProperty($this->provider, 'accessTokenURL', 'https://localhost/oauth1/access_token');
		$this->setProperty($this->provider, 'apiURL', 'https://localhost/oauth1/api');

	}

	public function testOAuth1Instance():void{
		$this::assertInstanceOf(OAuth1Interface::class, $this->provider);
	}

	public function testGetAuthURL():void{
		parse_str(parse_url($this->provider->getAuthURL(), PHP_URL_QUERY), $query);

		$this::assertSame('test_request_token', $query['oauth_token']);
	}

	public function testGetSignature():void{
		$signature = $this
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

		$this
			->getMethod('getSignature')
			->invokeArgs($this->provider, ['whatever', [], 'GET']);
	}

	public function testGetAccessToken():void{
		$token = new AccessToken(['accessTokenSecret' => 'test_request_token_secret']);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$token = $this->provider->getAccessToken('test_request_token', 'verifier');

		$this::assertSame('test_access_token', $token->accessToken);
		$this::assertSame('test_access_token_secret', $token->accessTokenSecret);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this->getMethod('parseTokenResponse')->invokeArgs($this->provider, [$this->responseFactory->createResponse()]);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [
				$this->responseFactory->createResponse()->withBody(create_stream_from_input('error=whatever')),
			])
		;
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid token');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [
				$this->responseFactory->createResponse()->withBody(create_stream_from_input('oauth_token=whatever')),
			])
		;
	}

	public function testParseTokenResponseCallbackUnconfirmedException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('oauth callback unconfirmed');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [
				$this->responseFactory->createResponse()
					->withBody(create_stream_from_input('oauth_token=whatever&oauth_token_secret=whatever_secret')),
				true,
			])
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
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$this::assertSame('such data! much wow!', get_json($this->provider->request('/request'))->data);

		// coverage, @todo
		$this->provider
			->request('/request', null, 'POST', ['foo' => 'bar'], ['Content-Type' => 'application/json']);
		$this->provider
			->request('/request', null, 'POST', ['foo' => 'bar'], ['Content-Type' => 'application/x-www-form-urlencoded']);
	}

}
