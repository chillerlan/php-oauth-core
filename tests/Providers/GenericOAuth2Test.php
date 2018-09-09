<?php
/**
 * Class GenericOAuth2Test
 *
 * @filesource   GenericOAuth2Test.php
 * @created      09.09.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuthExamples\OAuth2Testprovider;

/**
 * @property \chillerlan\OAuthExamples\OAuth2Testprovider $provider
 */
class GenericOAuth2Test extends OAuth2ProviderTestAbstract{

	protected $FQN = OAuth2Testprovider::class;

}
