<?php
/**
 * Class MailChimpTest
 *
 * @created      16.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\OAuthException;
use chillerlan\OAuth\Providers\MailChimp;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\MailChimp $provider
 */
class MailChimpTest extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return MailChimp::class;
	}

	protected array $testResponses = [
		'/oauth2/access_token' =>
			'{"access_token":"test_access_token","expires_in":3600,"state":"test_state","scope":"some_scope other_scope"}',
		'/oauth2/metadata'     =>
			'{"metadata":"whatever"}',
		'/3.0/'                =>
			'{"data":"such data! much wow! (/3.0/)"}',
	];

	protected AccessToken $token;

	public function setUp():void{
		parent::setUp();

		$this->token = new AccessToken([
			'accessToken' => 'test_access_token_secret',
			'expires'     => 1,
			'extraParams' => ['dc' => 'bar'],
		]);
	}

	public function testRequest():void{
		$this->storage->storeAccessToken($this->token, $this->provider->serviceName);
		$this::assertSame('such data! much wow! (/3.0/)', MessageUtil::decodeJSON($this->provider->request('/3.0/'))->data);
	}

	public function testRequestInvalidAuthTypeException():void{
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('invalid auth type');

		$this->reflection->getProperty('authMethod')->setValue($this->provider, -1);

		$this->storage->storeAccessToken($this->token, $this->provider->serviceName);

		$this->provider->request('');
	}

	public function testGetTokenMetadata():void{
		$token = $this->provider->getTokenMetadata($this->token);

		$this::assertSame('whatever', $token->extraParams['metadata']);
	}

}
