<?php
/**
 * Class OAuth2Test
 *
 * @created      09.09.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
final class OAuth2Test extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return DummyOAuth2Provider::class;
	}

}
