<?php
/**
 * Class OAuth2APITestAbstract
 *
 * @filesource   OAuth2APITestAbstract.php
 * @created      09.04.2018
 * @package      chillerlan\OAuthTest\API
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\HTTP\HTTPClientInterface;
use chillerlan\OAuth\{
	Providers\ClientCredentials, Providers\OAuth2Interface, Storage\TokenStorageInterface, Token
};
use chillerlan\Traits\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 */
abstract class OAuth2APITestAbstract extends APITestAbstract{

	/**
	 * @var array
	 */
	protected $scopes = [];

	/**
	 * @inheritdoc
	 */
	protected function initProvider(HTTPClientInterface $http, TokenStorageInterface $storage, ContainerInterface $options, LoggerInterface $logger){
		return new $this->FQCN($http, $storage, $options, $logger, $this->scopes);
	}

	public function testOAuth2Instance(){
		$this->assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	public function testRequestCredentialsToken(){

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('not supported');
		}

		$token = $this->provider->getClientCredentialsToken();

		$this->assertInstanceOf(Token::class, $token);
		$this->assertInternalType('string', $token->accessToken);

		if($token->expires !== Token::EOL_NEVER_EXPIRES){
			$this->assertGreaterThan(time(), $token->expires);
		}

		print_r($token);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Providers\ProviderException
	 * @expectedExceptionMessage not supported
	 */
	public function testRequestCredentialsTokenNotSupportedException(){

		if($this->provider instanceof ClientCredentials){
			$this->markTestSkipped('does not apply');
		}

		$this->provider->getClientCredentialsToken();
	}

}
