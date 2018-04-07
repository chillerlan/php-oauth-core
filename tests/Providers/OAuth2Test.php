<?php
/**
 * Class OAuth2Test
 *
 * @filesource   OAuth2Test.php
 * @created      03.11.2017
 * @package      chillerlan\OAuthTest\Providers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\{
	OAuthOptions, Providers\ClientCredentials, Providers\CSRFToken, Providers\OAuth2Interface, Providers\TokenRefresh, Token
};
use chillerlan\HTTP\{
	HTTPClientInterface, HTTPClientAbstract, HTTPResponse, HTTPResponseInterface
};

/**
 * @property \chillerlan\OAuth\Providers\OAuth2Interface $provider
 */
abstract class OAuth2Test extends ProviderTestAbstract{

	const OAUTH2_RESPONSES = [
		'https://localhost/oauth2/access_token' => [
			'access_token' => 'test_access_token',
			'expires_in'   => 3600,
			'state'        => 'test_state',
		],
		'https://localhost/oauth2/refresh_token' =>  [
			'access_token' => 'test_refreshed_access_token',
			'expires_in'   => 60,
			'state'        => 'test_state',
		],
		'https://localhost/oauth2/client_credentials' => [
			'access_token' => 'test_client_credentials_token',
			'expires_in'   => 30,
			'state'        => 'test_state',
		],
		'https://localhost/oauth2/api/request' => [
			'data' => 'such data! much wow!'
		],
	];

	protected function setUp(){
		parent::setUp();

		$this->setProperty($this->provider, 'apiURL', 'https://localhost/oauth2/api/request');
		$this->setProperty($this->provider, 'accessTokenURL', 'https://localhost/oauth2/access_token');

		if($this->provider instanceof TokenRefresh){
			$this->setProperty($this->provider, 'refreshTokenURL', 'https://localhost/oauth2/refresh_token');
		}

		if($this->provider instanceof ClientCredentials){
			$this->setProperty($this->provider, 'clientCredentialsTokenURL', 'https://localhost/oauth2/client_credentials');
		}

		$this->storage->storeCSRFState($this->provider->serviceName, 'test_state');
	}

	protected function initHttp():HTTPClientInterface{
		return new class(new OAuthOptions) extends HTTPClientAbstract{
			public function request(string $url, array $params = null, string $method = null, $body = null, array $headers = null):HTTPResponseInterface{
				return new HTTPResponse(['body' => json_encode(OAuth2Test::OAUTH2_RESPONSES[$url])]);
			}
		};
	}

	public function testGetAuthURL(){
		$url = $this->provider->getAuthURL(['client_secret' => 'foo']);
		parse_str(parse_url($url, PHP_URL_QUERY), $query);

		$this->assertArrayNotHasKey('client_secret', $query);
		$this->assertSame($this->options->key, $query['client_id']);
		$this->assertSame('code', $query['response_type']);
		$this->assertSame(explode('?', $url)[0], $this->getProperty('authURL')->getValue($this->provider));
	}

	public function testGetAccessToken(){
		$token = $this->provider->getAccessToken('foo', 'test_state');

		$this->assertSame('test_access_token', $token->accessToken);
		$this->assertGreaterThan(time(), $token->expires);
	}

	public function testGetAccessTokenBody(){
		$body = $this->getMethod('getAccessTokenBody')->invokeArgs($this->provider, ['foo']);

		$this->assertSame('foo', $body['code']);
		$this->assertSame($this->options->key, $body['client_id']);
		$this->assertSame($this->options->secret, $body['client_secret']);
		$this->assertSame('authorization_code', $body['grant_type']);
	}

	public function testParseTokenResponse(){
		$token = $this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse(['body' => json_encode(['access_token' => 'whatever'])])]);

		$this->assertInstanceOf(Token::class, $token);
		$this->assertSame('whatever', $token->accessToken);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage unable to parse token response
	 */
	public function testParseTokenResponseNoData(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse(['body' => ''])]);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage retrieving access token:
	 */
	public function testParseTokenResponseError(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse(['body' => json_encode(['error' => 'whatever'])])]);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage token missing
	 */
	public function testParseTokenResponseNoToken(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [new HTTPResponse(['body' => json_encode(['foo' => 'bar'])])]);
	}

	public function testCheckCSRFState(){

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('N/A');
		}

		$provider = $this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['test_state']);

		$this->assertInstanceOf(OAuth2Interface::class, $provider);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage invalid state
	 */
	public function testCheckStateInvalid(){

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('N/A');
		}

		$this
			->getMethod('checkState')
			->invoke($this->provider);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage invalid CSRF state
	 */
	public function testCheckStateInvalidCSRFState(){

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('N/A');
		}

		$this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['invalid_test_state']);
	}

	public function testRequest(){
		$this->storeToken(new Token(['accessToken' => 'test_access_token_secret', 'expires' => 1]));

		$response = $this->provider->request('');

		$this->assertSame('such data! much wow!', $response->json->data);
	}

	/**
	 * @expectedException \chillerlan\OAuth\OAuthException
	 * @expectedExceptionMessage invalid auth type
	 */
	public function testRequestInvalidAuthType(){
		$this->setProperty($this->provider, 'authMethod', 'foo');

		$this->storeToken(new Token(['accessToken' => 'test_access_token_secret', 'expires' => 1]));
		$this->provider->request('');
	}

}
