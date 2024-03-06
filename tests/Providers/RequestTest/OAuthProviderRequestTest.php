<?php
/**
 * Class OAuthProviderRequestTest
 *
 * @created      17.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\RequestTest;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\{OAuthProviderTestAbstract, ProviderTestHttpClient};

final class OAuthProviderRequestTest extends OAuthProviderTestAbstract{

	protected string $FQN = RequestTestProvider::class;

	protected array $testResponses = [
		'/api/gimme' => 'much data',
	];

	protected function setUp():void{
		parent::setUp();

		$this->provider->storeAccessToken(new AccessToken(['accessToken' => 'foo']));
	}

	public function testRequestURI():void{
		// just the path segment
		$r = $this->provider->request('/api/gimme');
		$this::assertSame('much data', (string)$r->getBody());

		// host and full path
		$r = $this->provider->request('https://localhost/api/gimme');
		$this::assertSame('much data', (string)$r->getBody());

		// missing path segment
		$r = $this->provider->request('https://localhost/gimme');
		$this::assertSame(404, $r->getStatusCode());
	}

	public function testRequestBody():void{

		$r = $this->provider->request(
			ProviderTestHttpClient::ECHO_REQUEST,
			[],
			'POST',
			'payload',
			['content-type' => 'application/whatever']
		);

		$this::assertSame('payload', (string)$r->getBody());

		$r = $this->provider->request(
			ProviderTestHttpClient::ECHO_REQUEST,
			[],
			'POST',
			['data' => 'payload'],
			['content-type' => 'application/json']
		);

		$this::assertSame('{"data":"payload"}', (string)$r->getBody());

		$r = $this->provider->request(
			ProviderTestHttpClient::ECHO_REQUEST,
			[],
			'POST',
			['data' => 'payload'],
			['content-type' => 'application/x-www-form-urlencoded']
		);

		$this::assertSame('data=payload', (string)$r->getBody());
	}

	public function testRequestHostMismatchException():void{
		$this::expectException(ProviderException::class);
		$this::expectExceptionMessage('given host (notlocalhost) does not match provider (localhost)');

		$this->provider->request('https://notlocalhost/api/gimme');
	}

}
