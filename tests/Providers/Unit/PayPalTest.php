<?php
/**
 * Class PayPalTest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\PayPal;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\PayPal $provider
 */
class PayPalTest extends OAuth2ProviderTestAbstract{

	protected function getProviderFQCN():string{
		return PayPal::class;
	}

}
