<?php
/**
 * Class GenericOAuth1Test
 *
 * @filesource   GenericOAuth1Test.php
 * @created      09.09.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuthExamples\OAuth1Testprovider;

/**
 * @property \chillerlan\OAuthExamples\OAuth1Testprovider $provider
 */
class GenericOAuth1Test extends OAuth1ProviderTestAbstract{

	protected $FQN = OAuth1Testprovider::class;

}
