<?php
/**
 * Class Deezer
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{AccessToken, CSRFToken, OAuth2Provider};
use Psr\Http\Message\{ResponseInterface, UriInterface};
use function array_merge, implode, sprintf;
use const PHP_QUERY_RFC1738;

/**
 * @see https://developers.deezer.com/api/oauth
 *
 * sure, you *can* use different parameter names than the standard ones... and what about JSON?
 * https://xkcd.com/927/
 */
class Deezer extends OAuth2Provider implements CSRFToken{

	public const SCOPE_BASIC             = 'basic_access';
	public const SCOPE_EMAIL             = 'email';
	public const SCOPE_OFFLINE_ACCESS    = 'offline_access';
	public const SCOPE_MANAGE_LIBRARY    = 'manage_library';
	public const SCOPE_MANAGE_COMMUNITY  = 'manage_community';
	public const SCOPE_DELETE_LIBRARY    = 'delete_library';
	public const SCOPE_LISTENING_HISTORY = 'listening_history';

	public const DEFAULT_SCOPES = [
		self::SCOPE_BASIC,
		self::SCOPE_EMAIL,
		self::SCOPE_OFFLINE_ACCESS,
		self::SCOPE_MANAGE_LIBRARY,
		self::SCOPE_LISTENING_HISTORY,
	];

	public const AUTH_METHOD = self::AUTH_METHOD_QUERY;

	protected string      $authURL        = 'https://connect.deezer.com/oauth/auth.php';
	protected string      $accessTokenURL = 'https://connect.deezer.com/oauth/access_token.php';
	protected string      $apiURL         = 'https://api.deezer.com';
	protected string|null $userRevokeURL  = 'https://www.deezer.com/account/apps';
	protected string|null $apiDocs        = 'https://developers.deezer.com/api';
	protected string|null $applicationURL = 'http://developers.deezer.com/myapps';

	/**
	 * @inheritDoc
	 */
	public function getAuthURL(array|null $params = null, array|null $scopes = null):UriInterface{
		$params ??= [];

		if(isset($params['client_secret'])){
			unset($params['client_secret']);
		}

		$params = array_merge($params, [
			'app_id'       => $this->options->key,
			'redirect_uri' => $this->options->callbackURL,
			'perms'        => implode($this::SCOPE_DELIMITER, ($scopes ?? [])),
		]);

		$params = $this->setState($params);

		return $this->uriFactory->createUri(QueryUtil::merge($this->authURL, $params));
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken{
		$this->checkState($state);

		$body = [
			'app_id' => $this->options->key,
			'secret' => $this->options->secret,
			'code'   => $code,
			'output' => 'json',
		];

		$request = $this->requestFactory
			->createRequest('POST', $this->accessTokenURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withHeader('Accept-Encoding', 'identity')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)));

		$token = $this->parseTokenResponse($this->http->sendRequest($request));

		$this->storage->storeAccessToken($token, $this->serviceName);

		return $token;
	}

	/**
	 * @inheritDoc
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{
		$data = QueryUtil::parse(MessageUtil::decompress($response));

		if(isset($data['error_reason'])){
			throw new ProviderException('error retrieving access token: "'.$data['error_reason'].'"');
		}

		if(!isset($data['access_token'])){
			throw new ProviderException('token missing');
		}

		$token = $this->createAccessToken();

		$token->accessToken  = $data['access_token'];
		$token->expires      = (int)($data['expires'] ?? $data['expires_in'] ?? AccessToken::EOL_NEVER_EXPIRES);
		$token->refreshToken = ($data['refresh_token'] ?? null);

		unset($data['expires'], $data['expires_in'], $data['refresh_token'], $data['access_token']);

		$token->extraParams = $data;

		return $token;
	}

	/**
	 * deezer keeps testing my sanity - HTTP/200 on invalid token... sure
	 *
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/user/me');
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response);

		if($status === 200 && !isset($json->error)){
			return $response;
		}

		if(isset($json->error)){
			throw new ProviderException($json->error->message);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
