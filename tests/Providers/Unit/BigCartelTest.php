<?php
/**
 * Class BigCartelTest
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\BigCartel;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\BigCartel $provider
 */
class BigCartelTest extends OAuth2ProviderTestAbstract{

	protected const TEST_RESPONSES = [
		'/oauth2/access_token'       =>
			'{"access_token":"test_access_token","expires_in":3600,"state":"test_state","scope":"some_scope other_scope"}',
		'/oauth2/client_credentials' =>
			'{"access_token":"test_client_credentials_token","expires_in":30,"state":"test_state"}',
		'/oauth2/api/request'        =>
			'{"data":"such data! much wow!"}',
		'/oauth2/api/accounts'       =>
			'{"data":[{"id":"12345"}]}',
		'/oauth2/revoke_token/12345' =>
			'',
	];

	protected function getProviderFQCN():string{
		return BigCartel::class;
	}

}
