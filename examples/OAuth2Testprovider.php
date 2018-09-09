<?php
/**
 * Class OAuth2Testprovider
 *
 * @filesource   OAuth2Testprovider.php
 * @created      06.04.2018
 * @package      chillerlan\OAuthExamples
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthExamples;

use chillerlan\OAuth\Core\{
	ClientCredentials, CSRFToken, OAuth2CSRFTokenTrait, OAuth2ClientCredentialsTrait,
	OAuth2Provider, OAuth2TokenRefreshTrait, TokenExpires, TokenRefresh, AccessTokenForRefresh
};

class OAuth2Testprovider extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenExpires, TokenRefresh, AccessTokenForRefresh{
	use OAuth2CSRFTokenTrait, OAuth2ClientCredentialsTrait, OAuth2TokenRefreshTrait;

	protected $apiURL         = 'https://api.example.com/';
	protected $authURL        = 'https://example.com/oauth2/authorize';
	protected $accessTokenURL = 'https://example.com/oauth2/token';
	protected $userRevokeURL  = 'https://account.example.com/apps/';
	protected $endpointMap     = TestEndpoints::class;
	protected $authHeaders    = ['foo' => 'bar'];
	protected $apiHeaders     = ['foo' => 'bar'];

}
