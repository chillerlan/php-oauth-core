<?php
/**
 * Class FlickrTest
 *
 * @created      01.01.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Flickr;
use chillerlan\OAuthTest\Providers\OAuth1ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Flickr $provider
 */
class FlickrTest extends OAuth1ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return Flickr::class;
	}

	protected array $testResponses = [
		'/oauth1/request_token' => 'oauth_token=test_request_token&oauth_token_secret=test_request_token_secret&oauth_callback_confirmed=true',
		'/oauth1/access_token'  => 'oauth_token=test_access_token&oauth_token_secret=test_access_token_secret&oauth_callback_confirmed=true',
		// the Flickr client does not add a path, so "/request" is missing
		'/oauth1/api'           => '{"data":"such data! much wow!"}',
	];

}
