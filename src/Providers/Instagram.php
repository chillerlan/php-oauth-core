<?php
/**
 * Class Instagram
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{CSRFToken, OAuth2Provider, ProviderException};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * @todo: instagram API has changed entirely and i won't bother fixing it because reasons
 *
 * @see https://developers.facebook.com/docs/instagram
 * @see https://developers.facebook.com/docs/instagram-basic-display-api/reference/oauth-authorize
 */
class Instagram extends OAuth2Provider implements CSRFToken{

	public const SCOPE_BASIC          = 'basic';
	public const SCOPE_COMMENTS       = 'comments';
	public const SCOPE_RELATIONSHIPS  = 'relationships';
	public const SCOPE_LIKES          = 'likes';
	public const SCOPE_PUBLIC_CONTENT = 'public_content';
	public const SCOPE_FOLLOWER_LIST  = 'follower_list';

	protected array $defaultScopes    = [
		self::SCOPE_BASIC,
		self::SCOPE_PUBLIC_CONTENT,
	];

	protected string      $authURL        = 'https://api.instagram.com/oauth/authorize';
	protected string      $accessTokenURL = 'https://api.instagram.com/oauth/access_token';
	protected string      $apiURL         = 'https://api.instagram.com';
	protected string|null $userRevokeURL  = 'https://www.instagram.com/accounts/manage_access/';
	protected string|null $apiDocs        = 'https://www.instagram.com/developer/';
	protected string|null $applicationURL = 'https://www.instagram.com/developer/clients/manage/';
#	protected int         $authMethod     = self::AUTH_METHOD_QUERY;

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/me/');
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
