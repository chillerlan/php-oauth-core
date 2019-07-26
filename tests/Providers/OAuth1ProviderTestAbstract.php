<?php
/**
 * Class OAuth1ProviderTestAbstract
 *
 * @filesource   OAuth1ProviderTestAbstract.php
 * @created      09.09.2018
 * @package      chillerlan\OAuthTest\Providers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\Psr17;
use chillerlan\HTTP\Psr7;
use chillerlan\HTTP\Psr7\{Request, Response};
use chillerlan\OAuth\Core\{AccessToken, OAuth1Interface, ProviderException};

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
abstract class OAuth1ProviderTestAbstract extends ProviderTestAbstract{

	protected $responses = [
		'/oauth1/request_token' => 'oauth_token=test_request_token&oauth_token_secret=test_request_token_secret&oauth_callback_confirmed=true',
		'/oauth1/access_token'  => 'oauth_token=test_access_token&oauth_token_secret=test_access_token_secret&oauth_callback_confirmed=true',
		'/oauth1/api/request'   => '{"data":"such data! much wow!"}',
	];

	protected function setUp():void{
		parent::setUp();

		$this->setProperty($this->provider, 'requestTokenURL', 'https://localhost/oauth1/request_token');
		$this->setProperty($this->provider, 'accessTokenURL', 'https://localhost/oauth1/access_token');
		$this->setProperty($this->provider, 'apiURL', 'https://localhost/oauth1/api/request');

	}

	public function testOAuth1Instance(){
		$this->assertInstanceOf(OAuth1Interface::class, $this->provider);
	}

	public function testGetAuthURL(){
		\parse_str(\parse_url($this->provider->getAuthURL(), \PHP_URL_QUERY), $query);

		$this->assertSame('test_request_token', $query['oauth_token']);
	}

	public function testGetSignature(){
		$signature = $this
			->getMethod('getSignature')
			->invokeArgs($this->provider, ['http://localhost/api/whatever', ['foo' => 'bar', 'oauth_signature' => 'should not see me!'], 'GET']);

		$this->assertSame('ygg22quLhpyegiyr7yl4hLAP9S8=', $signature);
	}

	public function testGetSignatureInvalidURLException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('getSignature: invalid url');

		$this
			->getMethod('getSignature')
			->invokeArgs($this->provider, ['whatever', [], 'GET']);
	}

	public function testGetAccessToken(){
		$token = new AccessToken(['accessTokenSecret' => 'test_request_token_secret']);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$token = $this->provider->getAccessToken('test_request_token', 'verifier');

		$this->assertSame('test_access_token', $token->accessToken);
		$this->assertSame('test_access_token_secret', $token->accessTokenSecret);
	}

	public function testParseTokenResponseNoDataException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this->getMethod('parseTokenResponse')->invokeArgs($this->provider, [new Response]);
	}

	public function testParseTokenResponseErrorException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input('error=whatever'))])
		;
	}

	public function testParseTokenResponseNoTokenException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid token');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input('oauth_token=whatever'))])
		;
	}

	public function testParseTokenResponseCallbackUnconfirmedException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('oauth callback unconfirmed');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input('oauth_token=whatever&oauth_token_secret=whatever_secret')), true])
		;
	}

	public function testGetRequestAuthorization(){

		$authHeader = $this->provider
			->getRequestAuthorization(
				new Request('GET', 'https://foo.bar'),
				new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token'])
			)
			->getHeaderLine('Authorization');

		$this->assertStringContainsString('OAuth oauth_consumer_key="'.$this->options->key.'"', $authHeader);
		$this->assertStringContainsString('oauth_token="test_token"', $authHeader);
	}

	public function testRequest(){
		$token = new AccessToken(['accessTokenSecret' => 'test_request_token_secret']);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$this->assertSame('such data! much wow!', Psr7\get_json($this->provider->request(''))->data);
	}

}
