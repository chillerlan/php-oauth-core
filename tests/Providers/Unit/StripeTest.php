<?php
/**
 * Class StripeTest
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Stripe;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Stripe $provider
 */
class StripeTest extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return Stripe::class;
	}

}
