<?php
/**
 * Trait OAuthOptionsTrait
 *
 * @filesource   OAuthOptionsTrait.php
 * @created      29.01.2018
 * @package      chillerlan\OAuth
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth;

trait OAuthOptionsTrait{

	/**
	 * The application key (or id) given by your provider
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The application secret given by your provider
	 *
	 * @var string
	 */
	protected $secret;

	/**
	 * The callback URL associated with your application
	 *
	 * @var string
	 */
	protected $callbackURL;

	/**
	 * Whether or not to start the session when session storage is used
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 *
	 * @var bool
	 */
	protected $sessionStart = true;

	/**
	 * The session array key for token storage
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 *
	 * @var string
	 */
	protected $sessionTokenVar = 'chillerlan-oauth-token';

	/**
	 * The session array key for <state> storage (OAuth2)
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 *
	 * @var string
	 */
	protected $sessionStateVar = 'chillerlan-oauth-state';

	/**
	 * Whether or not to automatically refresh access tokens (OAuth2)
	 *
	 * @var bool
	 */
	protected $tokenAutoRefresh = true;

}
