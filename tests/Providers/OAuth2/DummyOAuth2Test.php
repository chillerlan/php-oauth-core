<?php
/**
 * Class DummyOAuth2Test
 *
 * @created      09.09.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\OAuth2;

use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;
use chillerlan\OAuth\Core\{ClientCredentials, CSRFToken, OAuth2Provider, OAuthInterface, TokenRefresh};
use ReflectionClass;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
final class DummyOAuth2Test extends OAuth2ProviderTestAbstract{

	protected string $FQN = DummyOAuth2Provider::class;

}