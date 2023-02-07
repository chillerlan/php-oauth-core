<?php
/**
 * Class OAuthProviderTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\AccessToken;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

abstract class OAuthProviderTestAbstract extends ProviderTestAbstract{

	public const ECHO_REQUEST = '/__echo__';

	protected function setUp():void{
		parent::setUp();

		$this->storage->storeAccessToken($this->provider->serviceName, new AccessToken(['accessToken' => 'foo']));
	}

	protected function initHttp(SettingsContainerInterface $options, LoggerInterface $logger, array $responses):ClientInterface{
		return new ProviderTestHttpClient($responses, $this->responseFactory, $this->streamFactory);
	}

}
