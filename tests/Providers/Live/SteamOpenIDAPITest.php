<?php
/**
 * Class SteamOpenIDAPITest
 *
 * @created      15.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\SteamOpenID;
use chillerlan\OAuthTest\Providers\OAuthAPITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\SteamOpenID $provider
 */
class SteamOpenIDAPITest extends OAuthAPITestAbstract{

	protected string $ENV = 'STEAMOPENID';

	protected int $id;

	protected function setUp():void{
		parent::setUp();

		$token = $this->storage->getAccessToken($this->provider->serviceName);

		$this->id = $token->extraParams['id_int']; // SteamID64
	}

	protected function getProviderFQCN():string{
		return SteamOpenID::class;
	}

}
