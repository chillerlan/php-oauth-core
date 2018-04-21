<?php
/**
 * Class OAuth1Testprovider
 *
 * @filesource   OAuth1Testprovider.php
 * @created      06.04.2018
 * @package      chillerlan\OAuthExamples
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthExamples;

use chillerlan\OAuth\Core\OAuth1Provider;

/**
 *
 */
class OAuth1Testprovider extends OAuth1Provider{

	protected $apiURL          = 'https://api.example.com';
	protected $requestTokenURL = 'https://example.com/oauth/request_token';
	protected $authURL         = 'https://example.com/oauth/authorize';
	protected $accessTokenURL  = 'https://example.com/oauth/access_token';
	protected $userRevokeURL   = 'https://account.example.com/apps/';

}
