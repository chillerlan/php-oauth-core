<?php
/**
 * Class PayPal
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, CSRFToken, OAuth2Provider, TokenRefresh};
use Psr\Http\Message\ResponseInterface;
use function array_column, base64_encode, explode, implode, is_array, json_decode, sprintf;
use const PHP_QUERY_RFC1738;

/**
 * @see https://developer.paypal.com/docs/connect-with-paypal/integrate/
 */
class PayPal extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh{

	public const SCOPE_BASIC_AUTH     = 'openid';
	public const SCOPE_FULL_NAME      = 'profile';
	public const SCOPE_EMAIL          = 'email';
	public const SCOPE_ADDRESS        = 'address';
	public const SCOPE_ACCOUNT        = 'https://uri.paypal.com/services/paypalattributes';

	protected array $defaultScopes = [
		self::SCOPE_BASIC_AUTH,
		self::SCOPE_EMAIL,
	];

	protected string      $accessTokenURL = 'https://api.paypal.com/v1/oauth2/token';
	protected string      $authURL        = 'https://www.paypal.com/connect';
	protected string      $apiURL         = 'https://api.paypal.com';
	protected string|null $applicationURL = 'https://developer.paypal.com/developer/applications/';
	protected string|null $apiDocs        = 'https://developer.paypal.com/docs/connect-with-paypal/reference/';

	/**
	 * @inheritDoc
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{
		$data = json_decode(MessageUtil::decompress($response), true);

		if(!is_array($data)){
			throw new ProviderException('unable to parse token response');
		}

		if(isset($data['error'])){
			throw new ProviderException(sprintf('error retrieving access token: "%s"',  $data['error']));
		}

		// @codeCoverageIgnoreStart
		if(isset($data['name'], $data['message'])){
			$msg = sprintf('error retrieving access token: "%s" [%s]', $data['message'], $data['name']);

			if(isset($data['links']) && is_array($data['links'])){
				$msg .= "\n".implode("\n", array_column($data['links'], 'href'));
			}

			throw new ProviderException($msg);
		}
		// @codeCoverageIgnoreEnd

		if(!isset($data['access_token'])){
			throw new ProviderException('token missing');
		}

		$token = $this->createAccessToken();

		$token->accessToken  = $data['access_token'];
		$token->expires      = ($data['expires_in'] ?? AccessToken::EOL_NEVER_EXPIRES);
		$token->refreshToken = ($data['refresh_token'] ?? null);
		$token->scopes       = explode($this->scopesDelimiter, ($data['scope'] ?? ''));

		unset($data['expires_in'], $data['refresh_token'], $data['access_token'], $data['scope']);

		$token->extraParams = $data;

		return $token;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken{
		$this->checkState($state); // we're an instance of CSRFToken

		$body = [
			'code'         => $code,
			'grant_type'   => 'authorization_code',
			'redirect_uri' => $this->options->callbackURL,
		];

		$request = $this->requestFactory
			->createRequest('POST', $this->accessTokenURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Authorization', 'Basic '.base64_encode($this->options->key.':'.$this->options->secret))
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)));

		$token = $this->parseTokenResponse($this->http->sendRequest($request));

		$this->storage->storeAccessToken($token, $this->serviceName);

		return $token;
	}

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/v1/identity/oauth2/userinfo', ['schema' => 'paypalv1.1']);
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
