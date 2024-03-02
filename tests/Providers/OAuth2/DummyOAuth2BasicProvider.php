<?php
/**
 * Class DummyOAuth2BasicProvider
 *
 * @created      16.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\OAuth2;

use chillerlan\OAuth\Core\OAuth2Provider;

/**
 * A minimal OAuth2 provider implementation
 */
final class DummyOAuth2BasicProvider extends OAuth2Provider{

	protected string  $authURL        = 'https://example.com/oauth2/authorize';
	protected string  $accessTokenURL = 'https://example.com/oauth2/token';
	protected string  $apiURL         = 'https://api.example.com/';
	protected ?string $userRevokeURL  = 'https://account.example.com/apps/';

}
