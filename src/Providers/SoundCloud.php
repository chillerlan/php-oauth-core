<?php
/**
 * Class SoundCloud
 *
 * @created      22.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{ClientCredentials, OAuth2Provider, TokenRefresh};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * @see https://developers.soundcloud.com/
 * @see https://developers.soundcloud.com/docs/api/guide#authentication
 * @see https://developers.soundcloud.com/blog/security-updates-api
 */
class SoundCloud extends OAuth2Provider implements ClientCredentials, TokenRefresh{

	public const SCOPE_NONEXPIRING      = 'non-expiring';
#	public const SCOPE_EMAIL            = 'email'; // ???

	protected array   $defaultScopes    = [
		self::SCOPE_NONEXPIRING,
	];

	protected string      $authURL          = 'https://api.soundcloud.com/connect';
	protected string      $accessTokenURL   = 'https://api.soundcloud.com/oauth2/token';
	protected string      $apiURL           = 'https://api.soundcloud.com';
	protected string|null $userRevokeURL    = 'https://soundcloud.com/settings/connections';
	protected string|null $apiDocs          = 'https://developers.soundcloud.com/';
	protected string|null $applicationURL   = 'https://soundcloud.com/you/apps';
	protected string      $authMethodHeader = 'OAuth';

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/me');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->status)){
			throw new ProviderException($json->status);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
