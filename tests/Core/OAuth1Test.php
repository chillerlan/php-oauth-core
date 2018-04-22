<?php
/**
 * Class OAuth1Test
 *
 * @filesource   OAuth1Test.php
 * @created      03.11.2017
 * @package      chillerlan\OAuthTest\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\HTTP\{
	HTTPClientAbstract, HTTPClientInterface, HTTPResponse, HTTPResponseInterface
};
use chillerlan\OAuth\{
	Core\AccessToken, OAuthOptions
};

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
abstract class OAuth1Test extends ProviderTestAbstract{

	const OAUTH1_RESPONSES = [
		'https://localhost/oauth1/request_token' => 'oauth_token=test_request_token&oauth_token_secret=test_request_token_secret&oauth_callback_confirmed=true',
		'https://localhost/oauth1/access_token' => 'oauth_token=test_access_token&oauth_token_secret=test_access_token_secret&oauth_callback_confirmed=true',
		'https://localhost/oauth1/api/request' => '{"data":"such data! much wow!"}',
	];


	protected function setUp(){
		parent::setUp();

		$this->setProperty($this->provider, 'requestTokenURL', 'https://localhost/oauth1/request_token');
		$this->setProperty($this->provider, 'accessTokenURL', 'https://localhost/oauth1/access_token');
		$this->setProperty($this->provider, 'apiURL', 'https://localhost/oauth1/api/request');

	}

	protected function initHttp():HTTPClientInterface{
		return new class(new OAuthOptions) extends HTTPClientAbstract{
			protected function getResponse():HTTPResponseInterface{
				return new HTTPResponse(['body' => OAuth1Test::OAUTH1_RESPONSES[$this->requestURL]]);
			}
		};
	}

	public function testParseTokenResponse(){
		$token = $this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse(['body' => 'oauth_token=whatever&oauth_token_secret=whatever_secret&oauth_callback_confirmed=true'])]);

		$this->assertInstanceOf(AccessToken::class, $token);
		$this->assertSame('whatever', $token->accessToken);
		$this->assertSame('whatever_secret', $token->accessTokenSecret);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage oauth callback unconfirmed
	 */
	public function testParseTokenResponseCallbackUnconfirmed(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse(['body' => 'oauth_token=whatever&oauth_token_secret=whatever_secret']), true]);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage unable to parse token response
	 */
	public function testParseTokenResponseNoData(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse]);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage error retrieving access token
	 */
	public function testParseTokenResponseError(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse(['body' => 'error=whatever'])]);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage token missing
	 */
	public function testParseTokenResponseNoToken(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse(['body' => 'oauth_token=whatever'])]);
	}

	public function testGetRequestTokenHeaderParams(){
		$params = $this
			->getMethod('getRequestTokenHeaderParams')
			->invoke($this->provider);

		$this->assertSame('https://localhost/callback', $params['oauth_callback']);
		$this->assertSame($this->options->key, $params['oauth_consumer_key']);
		$this->assertRegExp('/^([a-f\d]{64})$/', $params['oauth_nonce']);
	}

	public function testRequestHeaders(){
		$headers = $this
			->getMethod('requestHeaders')
			->invokeArgs($this->provider, ['http://localhost/api/whatever', ['oauth_session_handle' => 'nope'], 'GET', [], new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token'])]);

		$this->assertContains('OAuth oauth_consumer_key="'.$this->options->key.'", oauth_nonce="', $headers['Authorization']);
	}

	public function testGetAccessTokenHeaders(){
		$headers = $this
			->getMethod('getAccessTokenHeaders')
			->invokeArgs($this->provider, [['foo' => 'bar']]);

		$this->assertContains('OAuth oauth_consumer_key="'.$this->options->key.'", oauth_nonce="', $headers['Authorization']);
	}

	public function testGetSignatureData(){
		$signature = $this
			->getMethod('getSignatureData')
			->invokeArgs($this->provider, ['http://localhost/api/whatever', ['foo' => 'bar', 'oauth_signature' => 'should not see me!'], 'GET']);

		$this->assertSame('GET&http%3A%2F%2Flocalhost%2Fapi%2Fwhatever&foo%3Dbar', $signature);
	}

	public function testGetSignature(){
		$signature = $this
			->getMethod('getSignature')
			->invokeArgs($this->provider, ['http://localhost/api/whatever', ['foo' => 'bar', 'oauth_signature' => 'should not see me!'], 'GET']);

		$this->assertSame('ygg22quLhpyegiyr7yl4hLAP9S8=', $signature);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage getSignature: invalid url
	 */
	public function testGetSignatureInvalidURLException(){
		$this
			->getMethod('getSignature')
			->invokeArgs($this->provider, ['whatever', [], 'GET']);
	}

	public function testGetAuthURL(){
		parse_str(parse_url($this->provider->getAuthURL(), PHP_URL_QUERY), $query);

		$this->assertSame('test_request_token', $query['oauth_token']);
	}

	public function testGetAccessToken(){
		$this->storeToken(new AccessToken(['requestTokenSecret' => 'test_request_token_secret']));

		$token = $this->provider->getAccessToken('test_request_token', 'verifier');

		$this->assertSame('test_access_token', $token->accessToken);
		$this->assertSame('test_access_token_secret', $token->accessTokenSecret);
	}

	public function testRequest(){
		$this->storeToken(new AccessToken(['requestTokenSecret' => 'test_request_token_secret']));

		$response = $this->provider->request('');

		$this->assertSame('such data! much wow!', $response->json->data);
	}

}
