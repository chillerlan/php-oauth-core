<?php
/**
 * Class PayPalSandbox
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

/**
 *
 */
class PayPalSandbox extends PayPal{

	protected string $authURL        = 'https://www.sandbox.paypal.com/connect';
	protected string $accessTokenURL = 'https://api.sandbox.paypal.com/v1/oauth2/token';
	protected string $apiURL         = 'https://api.sandbox.paypal.com';

}
