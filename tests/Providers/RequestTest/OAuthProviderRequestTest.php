<?php
/**
 * Class OAuthProviderRequestTest
 *
 * @created      17.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\RequestTest;

use chillerlan\OAuth\Core\ProviderException;
use chillerlan\OAuth\MagicAPI\ApiClientException;
use chillerlan\OAuthTest\Providers\OAuthProviderTestAbstract;

final class OAuthProviderRequestTest extends OAuthProviderTestAbstract{

	protected string $FQN = RequestTestProvider::class;

	protected array $testResponses = [
		'/api/gimme' => 'much data',
	];

	public function testRequestURI():void{
		// just the path segemt
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
		$r = $this->provider->request($this::ECHO_REQUEST, [], 'POST', 'payload', ['content-type' => 'application/whatever']);
		$this::assertSame('payload', (string)$r->getBody());

		$r = $this->provider->request(
			$this::ECHO_REQUEST, [], 'POST', ['data' => 'payload'], ['content-type' => 'application/json']
		);
		$this::assertSame('{"data":"payload"}', (string)$r->getBody());

		$r = $this->provider->request(
			$this::ECHO_REQUEST, [], 'POST', ['data' => 'payload'], ['content-type' => 'application/x-www-form-urlencoded']
		);
		$this::assertSame('data=payload', (string)$r->getBody());
	}

	public function testRequestInvalidPathException():void{
		$this::expectException(ProviderException::class);
		$this::expectExceptionMessage('invalid path');

		$this->provider->request('?query');
	}

	public function testRequestHostMismatchException():void{
		$this::expectException(ProviderException::class);
		$this::expectExceptionMessage('given host does not match provider host');

		$this->provider->request('https://notlocalhost/api/gimme');
	}

	public function testCallMagicEndpoints():void{
		// get with path segment and query
		$r = $this->provider->test('withpath', 'andquery', ['foo' => 'bar', 'this-param' => 'does not exist']);
		$this::assertSame(
			'https://localhost/api/test/withpath/andquery'.$this::ECHO_REQUEST.'?foo=bar',
			$r->getHeaderLine('x-request-uri')
		);

		$params       = ['query' => 'whatever'];
		$body         = ['foo' => 'bar'];
		$expectedBody = '{"foo":"bar"}';

		// body only
		$r = $this->provider->postNoPathNoQuery($body);
		$this::assertSame($expectedBody, (string)$r->getBody());
		$this::assertSame(
			'https://localhost/api/post/nopathnoquery'.$this::ECHO_REQUEST,
			$r->getHeaderLine('x-request-uri')
		);

		// path segment and body
		$r = $this->provider->postWithPathNoQuery('withpathnoquery', $body);
		$this::assertSame($expectedBody, (string)$r->getBody());
		$this::assertSame(
			'https://localhost/api/post/withpathnoquery'.$this::ECHO_REQUEST,
			$r->getHeaderLine('x-request-uri')
		);

		// query parameters and body
		$r = $this->provider->postNoPathWithQuery($params, $body);
		$this::assertSame($expectedBody, (string)$r->getBody());
		$this::assertSame(
			'https://localhost/api/post/nopathwithquery'.$this::ECHO_REQUEST.'?query=whatever',
			$r->getHeaderLine('x-request-uri')
		);

		// path segment, query params and body
		$r = $this->provider->postWithPathAndQuery('withpathandquery', $params, $body);
		$this::assertSame($expectedBody, (string)$r->getBody());
		$this::assertSame(
			'https://localhost/api/post/withpathandquery'.$this::ECHO_REQUEST.'?query=whatever',
			$r->getHeaderLine('x-request-uri')
		);

	}

	public function testCallMagicEndpointNotFoundException():void{
		$this::expectException(ApiClientException::class);
		$this::expectExceptionMessage('endpoint not found: "whatever"');

		$this->provider->whatever();
	}

	public function testCallMagicEndpointTooFewUrlParamsException():void{
		$this::expectException(ApiClientException::class);
		$this::expectExceptionMessage('too few URL params, required: a, b');

		$this->provider->test();
	}

	public function testCallMagicEndpointInvalidPathElementValueException():void{
		$this::expectException(ApiClientException::class);
		$this::expectExceptionMessage('invalid path element value for "a": array');

		$this->provider->test([], []);
	}

}
