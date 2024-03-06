<?php
/**
 * Class LastFMTest
 *
 * @created      05.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\LastFM;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuthProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\LastFM $provider
 */
class LastFMTest extends OAuthProviderTestAbstract{

	protected function getProviderFQCN():string{
		return LastFM::class;
	}

	protected array $testResponses = [
		'/lastfm/auth'        => '{"session":{"key":"session_key"}}',
		'/lastfm/api/request' => '{"data":"such data! much wow!"}',
	];

	public function setUp():void{
		parent::setUp();

		$this->provider->storeAccessToken(new AccessToken(['accessToken' => 'foo']));
		$this->reflection->getProperty('apiURL')->setValue($this->provider, '/lastfm/api/request');
	}

	public function testGetAuthURL():void{
		$url = $this->provider->getAuthURL(['foo' => 'bar']);

		$this::assertSame('https://www.last.fm/api/auth?api_key='.$this->options->key.'&foo=bar', (string)$url);
	}

	public function testGetSignature():void{
		$signature = $this->reflection
			->getMethod('getSignature')
			->invokeArgs($this->provider, [['foo' => 'bar', 'format' => 'whatever', 'callback' => 'nope']]);

		$this::assertSame('cb143650fa678449f5492a2aa6fab216', $signature);
	}

	public function testParseTokenResponse():void{
		$r = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream('{"session":{"key":"whatever"}}'))
		;

		$token = $this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$r]);

		$this::assertSame('whatever', $token->accessToken);
	}

	public function testParseTokenResponseNoData():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$this->responseFactory->createResponse()]);
	}

	public function testParseTokenResponseError():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token:');

		$r = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream('{"error":42,"message":"whatever"}'))
		;

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$r]);
	}

	public function testParseTokenResponseNoToken():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token missing');

		$r = $this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('{"session":[]}'));

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$r]);
	}

	public function testGetAccessToken():void{
		$this->reflection->getProperty('apiURL')->setValue($this->provider, '/lastfm/auth');

		$token = $this->provider->getAccessToken('session_token');

		$this::assertSame('session_key', $token->accessToken);
	}

	// coverage
	public function testRequest():void{
		$r = $this->provider->request('');

		$this::assertSame('such data! much wow!', MessageUtil::decodeJSON($r)->data);
	}

	// coverage
	public function testRequestPost():void{
		$r = $this->provider->request('', [], 'POST', ['foo' => 'bar'], ['Content-Type' => 'whatever']);

		$this::assertSame('such data! much wow!', MessageUtil::decodeJSON($r)->data);
	}

}
