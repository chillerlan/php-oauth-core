<?php
/**
 * Class GenericOAuth1Test
 *
 * @filesource   GenericOAuth1Test.php
 * @created      06.04.2018
 * @package      chillerlan\OAuthTest\Providers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuthExamples\OAuth1Testprovider;

/**
 * @property \chillerlan\OAuthExamples\OAuth1Testprovider $provider
 */
class GenericOAuth1Test extends OAuth1Test{

	protected $FQCN = OAuth1Testprovider::class;

}
