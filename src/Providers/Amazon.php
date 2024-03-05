<?php
/**
 * Class Amazon
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{CSRFToken, OAuth2Provider, ProviderException, TokenRefresh};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * Amazon Login/OAuth
 *
 * @see https://login.amazon.com/
 * @see https://developer.amazon.com/docs/login-with-amazon/documentation-overview.html
 * @see https://images-na.ssl-images-amazon.com/images/G/01/lwa/dev/docs/website-developer-guide._TTH_.pdf
 * @see https://images-na.ssl-images-amazon.com/images/G/01/mwsportal/doc/en_US/offamazonpayments/LoginAndPayWithAmazonIntegrationGuide._V335378063_.pdf
 */
class Amazon extends OAuth2Provider implements CSRFToken, TokenRefresh{

	public const SCOPE_PROFILE         = 'profile';
	public const SCOPE_PROFILE_USER_ID = 'profile:user_id';
	public const SCOPE_POSTAL_CODE     = 'postal_code';

	protected array $defaultScopes = [
		self::SCOPE_PROFILE,
		self::SCOPE_PROFILE_USER_ID,
	];

	protected string      $authURL        = 'https://www.amazon.com/ap/oa';
	protected string      $accessTokenURL = 'https://www.amazon.com/ap/oatoken';
	protected string      $apiURL         = 'https://api.amazon.com';
	protected string|null $apiDocs        = 'https://login.amazon.com/';
	protected string|null $applicationURL = 'https://sellercentral.amazon.com/hz/home';

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/user/profile');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->error, $json->error_description)){
			throw new ProviderException($json->error_description);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
