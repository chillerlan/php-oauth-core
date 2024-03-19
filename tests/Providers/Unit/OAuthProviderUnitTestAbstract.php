<?php
/**
 * Class OAuthProviderUnitTestAbstract
 *
 * @created      18.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\Core\TokenInvalidate;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\ProviderUnitTestAbstract;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 *
 */
abstract class OAuthProviderUnitTestAbstract extends ProviderUnitTestAbstract{

	/*
	 * common unit tests
	 */

	public function testOAuthInstance():void{
		$this::assertInstanceOf(OAuthInterface::class, $this->provider);
	}

	public function testProviderInstance():void{
		$this::assertInstanceOf($this->getProviderFQCN(), $this->provider);
	}

	public function testMagicGet():void{
		$this::assertSame($this->reflection->getShortName(), $this->provider->serviceName);
		/** @noinspection PhpUndefinedFieldInspection */
		$this::assertNull($this->provider->foo);
	}

	/*
	 * request body
	 */



	/*
	 * request target
	 */

	public static function requestTargetProvider():array{
		return [
			'empty'          => ['', 'https://example.com/api'],
			'slash'          => ['/', 'https://example.com/api/'],
			'no slashes'     => ['a', 'https://example.com/api/a'],
			'leading slash'  => ['/b', 'https://example.com/api/b'],
			'trailing slash' => ['c/', 'https://example.com/api/c/'],
			'full url given' => ['https://example.com/other/path/d', 'https://example.com/other/path/d'],
			'ignore params'  => ['https://example.com/api/e/?with=param#foo', 'https://example.com/api/e/'],
			'subdomain'      => ['https://api.sub.example.com/a/b/c', 'https://api.sub.example.com/a/b/c'],
		];
	}

	#[DataProvider('requestTargetProvider')]
	public function testGetRequestTarget(string $path, string $expected):void{
		$this->setReflectionProperty('apiURL', 'https://example.com/api/');

		$this::assertSame($expected, $this->invokeReflectionMethod('getRequestTarget', [$path]));
	}

	public function testGetRequestTargetInvalidSchemeException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('scheme of the URL (http://example.com/boo) must be "https" if host is given');

		$this->invokeReflectionMethod('getRequestTarget', ['http://example.com/boo']);
	}

	public function testGetRequestTargetProviderMismatchException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('given host (nope.com) does not match provider');

		$this->invokeReflectionMethod('getRequestTarget', ['https://nope.com/ahrg']);
	}

	/*
	 * token invalidate
	 */

	public function testTokenInvalidate():void{

		if(!$this->provider instanceof TokenInvalidate){
			$this::markTestSkipped('TokenInvalidate N/A');
		}

		$this->storage->storeAccessToken(new AccessToken(['expires' => 42]), $this->provider->serviceName);

		$this::assertTrue($this->storage->hasAccessToken($this->provider->serviceName));
		$this::assertTrue($this->provider->invalidateAccessToken());
		$this::assertFalse($this->storage->hasAccessToken($this->provider->serviceName));
	}

}
