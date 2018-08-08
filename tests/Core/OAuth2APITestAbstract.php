<?php
/**
 * Class OAuth2APITestAbstract
 *
 * @filesource   OAuth2APITestAbstract.php
 * @created      09.04.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\HTTP\HTTPClientInterface;
use chillerlan\OAuth\{
	Core\OAuth2Interface, Storage\OAuthStorageInterface
};
use chillerlan\Traits\ImmutableSettingsInterface;
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
	protected function initProvider(HTTPClientInterface $http, OAuthStorageInterface $storage, ImmutableSettingsInterface $options, LoggerInterface $logger){
		return new $this->FQCN($http, $storage, $options, $logger, $this->scopes);
	}

	public function testOAuth2Instance(){
		$this->assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

}
