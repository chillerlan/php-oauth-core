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
	 * @var string
	 */
	protected $key;

	/**
	 * @var string
	 */
	protected $secret;

	/**
	 * @var string
	 */
	protected $callbackURL;

	/**
	 * @var bool
	 */
	protected $sessionStart = true;

	/**
	 * @var string
	 */
	protected $sessionTokenVar = 'chillerlan-oauth-token';

	/**
	 * @var string
	 */
	protected $sessionStateVar = 'chillerlan-oauth-state';

	/**
	 * @var bool
	 */
	protected $tokenAutoRefresh = true;

}
