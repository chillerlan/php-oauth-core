<?php
/**
 * Trait OAuthOptionsTrait
 *
 * @created      29.01.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth;

trait OAuthOptionsTrait{

	/**
	 * The application key (or id) given by your provider
	 */
	protected string $key;

	/**
	 * The application secret given by your provider
	 */
	protected string $secret;

	/**
	 * The callback URL associated with your application
	 */
	protected string $callbackURL;

	/**
	 * Whether or not to start the session when session storage is used
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected bool $sessionStart = true;

	/**
	 * The session array key for token storage
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected string $sessionTokenVar = 'chillerlan-oauth-token';

	/**
	 * The session array key for <state> storage (OAuth2)
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected string $sessionStateVar = 'chillerlan-oauth-state';

	/**
	 * Whether or not to automatically refresh access tokens (OAuth2)
	 */
	protected bool $tokenAutoRefresh = true;

}
