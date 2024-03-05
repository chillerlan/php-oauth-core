<?php
/**
 * Class GitHub
 *
 * @created      22.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{CSRFToken, OAuth2Provider, ProviderException, TokenRefresh};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * @see https://developer.github.com/apps/building-integrations/setting-up-and-registering-oauth-apps/
 * @see https://developer.github.com/v3/
 * @see https://docs.github.com/en/developers/apps/building-github-apps/refreshing-user-to-server-access-tokens
 */
class GitHub extends OAuth2Provider implements CSRFToken, TokenRefresh{

	public const SCOPE_USER             = 'user';
	public const SCOPE_USER_EMAIL       = 'user:email';
	public const SCOPE_USER_FOLLOW      = 'user:follow';
	public const SCOPE_PUBLIC_REPO      = 'public_repo';
	public const SCOPE_REPO             = 'repo';
	public const SCOPE_REPO_DEPLOYMENT  = 'repo_deployment';
	public const SCOPE_REPO_STATUS      = 'repo:status';
	public const SCOPE_REPO_INVITE      = 'repo:invite';
	public const SCOPE_REPO_DELETE      = 'delete_repo';
	public const SCOPE_NOTIFICATIONS    = 'notifications';
	public const SCOPE_GIST             = 'gist';
	public const SCOPE_REPO_HOOK_READ   = 'read:repo_hook';
	public const SCOPE_REPO_HOOK_WRITE  = 'write:repo_hook';
	public const SCOPE_REPO_HOOK_ADMIN  = 'admin:repo_hook';
	public const SCOPE_ORG_HOOK_ADMIN   = 'admin:org_hook';
	public const SCOPE_ORG_READ         = 'read:org';
	public const SCOPE_ORG_WRITE        = 'write:org';
	public const SCOPE_ORG_ADMIN        = 'admin:org';
	public const SCOPE_PUBLIC_KEY_READ  = 'read:public_key';
	public const SCOPE_PUBLIC_KEY_WRITE = 'write:public_key';
	public const SCOPE_PUBLIC_KEY_ADMIN = 'admin:public_key';
	public const SCOPE_GPG_KEY_READ     = 'read:gpg_key';
	public const SCOPE_GPG_KEY_WRITE    = 'write:gpg_key';
	public const SCOPE_GPG_KEY_ADMIN    = 'admin:gpg_key';

	protected array $defaultScopes = [
		self::SCOPE_USER,
		self::SCOPE_USER_EMAIL,
		self::SCOPE_PUBLIC_REPO,
		self::SCOPE_GIST,
	];

	protected string      $authURL        = 'https://github.com/login/oauth/authorize';
	protected string      $accessTokenURL = 'https://github.com/login/oauth/access_token';
	protected string      $apiURL         = 'https://api.github.com';
	protected string|null $userRevokeURL  = 'https://github.com/settings/applications';
	protected string|null $apiDocs        = 'https://developer.github.com/';
	protected string|null $applicationURL = 'https://github.com/settings/developers';
	protected array       $authHeaders    = ['Accept' => 'application/json'];
	protected array       $apiHeaders     = ['Accept' => 'application/vnd.github.beta+json'];

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/user');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->message)){
			throw new ProviderException($json->message);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
