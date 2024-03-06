<?php
/**
 * Class DeviantArtTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\DeviantArt;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\DeviantArt $provider
 */
class DeviantArtTest extends OAuth2ProviderTestAbstract{

	protected const TEST_RESPONSES = [
		'/oauth2/access_token'       =>
			'{"access_token":"test_access_token","expires_in":3600,"state":"test_state","scope":"some_scope other_scope"}',
		'/oauth2/refresh_token'      =>
			'{"access_token":"test_refreshed_access_token","expires_in":60,"state":"test_state"}',
		'/oauth2/revoke_token'       =>
			'{"success": true}',
		'/oauth2/client_credentials' =>
			'{"access_token":"test_client_credentials_token","expires_in":30,"state":"test_state"}',
		'/oauth2/api/request'        =>
			'{"data":"such data! much wow!"}',
	];

	protected function getProviderFQCN():string{
		return DeviantArt::class;
	}

}
