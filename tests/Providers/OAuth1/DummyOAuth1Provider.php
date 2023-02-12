<?php
/**
 * Class DummyOAuth1Provider
 *
 * @created      16.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\OAuth1;

use chillerlan\OAuth\Core\OAuth1Provider;

/**
 * An OAuth1 provider implementation
 */
final class DummyOAuth1Provider extends OAuth1Provider{

	protected string  $authURL         = 'https://example.com/oauth/authorize';
	protected string  $accessTokenURL  = 'https://example.com/oauth/access_token';
	protected string  $requestTokenURL = 'https://example.com/oauth/request_token';
	protected string  $apiURL          = 'https://api.example.com';
	protected ?string $userRevokeURL   = 'https://account.example.com/apps/';
	protected array   $authHeaders     = ['foo' => 'bar'];
	protected array   $apiHeaders      = ['foo' => 'bar'];

}
