<?php
/**
 * Class BattleNet
 *
 * @created      02.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{ClientCredentials, CSRFToken, OAuth2Provider};
use Psr\Http\Message\ResponseInterface;
use Throwable;
use function in_array, sprintf, strtolower;

/**
 * Battle.net OAuth
 *
 * @see https://develop.battle.net/documentation
 */
class BattleNet extends OAuth2Provider implements ClientCredentials, CSRFToken{

	public const SCOPE_OPENID      = 'openid';
	public const SCOPE_PROFILE_D3  = 'd3.profile';
	public const SCOPE_PROFILE_SC2 = 'sc2.profile';
	public const SCOPE_PROFILE_WOW = 'wow.profile';

	public const DEFAULT_SCOPES = [
		self::SCOPE_OPENID,
		self::SCOPE_PROFILE_D3,
		self::SCOPE_PROFILE_SC2,
		self::SCOPE_PROFILE_WOW,
	];

	protected string|null $apiDocs        = 'https://develop.battle.net/documentation';
	protected string|null $applicationURL = 'https://develop.battle.net/access/clients';
	protected string|null $userRevokeURL  = 'https://account.blizzard.com/connections';

	// the URL for the "OAuth" endpoints
	// @see https://develop.battle.net/documentation/battle-net/oauth-apis
	protected string $battleNetOauth      = 'https://oauth.battle.net';
	protected string $region              = 'eu';
	// these URLs will be set dynamically, depending on the chose datacenter
	protected string $apiURL              = 'https://eu.api.blizzard.com';
	protected string $authURL             = 'https://oauth.battle.net/authorize';
	protected string $accessTokenURL      = 'https://oauth.battle.net/token';

	/**
	 * Set the datacenter URLs for the given region
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function setRegion(string $region):static{
		$region = strtolower($region);

		if(!in_array($region, ['cn', 'eu', 'kr', 'tw', 'us'], true)){
			throw new ProviderException('invalid region: '.$region);
		}

		$this->region         = $region;
		$this->apiURL         = sprintf('https://%s.api.blizzard.com', $this->region);
		$this->battleNetOauth = 'https://oauth.battle.net';

		if($region === 'cn'){
			$this->apiURL         = 'https://gateway.battlenet.com.cn';
			$this->battleNetOauth = 'https://oauth.battlenet.com.cn';
		}

		$this->authURL        = $this->battleNetOauth.'/authorize';
		$this->accessTokenURL = $this->battleNetOauth.'/token';

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$token = $this->storage->getAccessToken($this->serviceName);
		$request  = $this->requestFactory->createRequest('GET', $this->battleNetOauth.'/oauth/userinfo');
		$response = $this->sendRequest($this->getRequestAuthorization($request, $token));
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		try{
			$json = MessageUtil::decodeJSON($response);
		}
		catch(Throwable){
		}

		if(isset($json->error, $json->error_description)){
			throw new ProviderException($json->error_description);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
