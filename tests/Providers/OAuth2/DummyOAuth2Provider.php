<?php
/**
 * Class DummyOAuth2Provider
 *
 * @created      16.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\OAuth2;

use chillerlan\OAuthTest\Providers\TestEndpoints;
use chillerlan\OAuth\Core\{ClientCredentials, CSRFToken, OAuth2Provider, TokenRefresh};

/**
 * An OAuth2 provider implementation that supports token refresh, csrf tokens and client credentials
 */
final class DummyOAuth2Provider extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh{

	protected string $authURL        = 'https://example.com/oauth2/authorize';
	protected string $accessTokenURL = 'https://example.com/oauth2/token';
	protected ?string $apiURL        = 'https://api.example.com/';
	protected ?string $userRevokeURL = 'https://account.example.com/apps/';
	protected ?string $endpointMap   = TestEndpoints::class;
	protected array $authHeaders     = ['foo' => 'bar'];
	protected array $apiHeaders      = ['foo' => 'bar'];
	protected int $authMethod        = OAuth2Provider::AUTH_METHOD_QUERY;

}
