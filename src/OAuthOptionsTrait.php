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
	protected $sandboxMode = false;

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
	protected $useEncryption;

	/**
	 * a 32 byte string, hex encoded
	 *
	 * @see sodium_crypto_box_secretkey()
	 *
	 * @var string
	 */
	protected $storageCryptoKey;

	/**
	 * @var bool
	 */
	protected $tokenAutoRefresh = false;

	/**
	 * @var string
	 */
	protected $dbLabelHashAlgo = 'md5';

	/**
	 * @var string
	 */
	protected $dbLabelFormat   = '%1$s@%2$s'; // user@service

	/**
	 * @var int|string
	 */
	protected $dbUserID;

	protected $dbTokenTable;
	protected $dbTokenTableExpires    = 'expires';
	protected $dbTokenTableLabel      = 'label';
	protected $dbTokenTableProviderID = 'provider_id';
	protected $dbTokenTableState      = 'state';
	protected $dbTokenTableToken      = 'token';
	protected $dbTokenTableUser       = 'user_id';

	protected $dbProviderTable;
	protected $dbProviderTableID      = 'provider_id';
	protected $dbProviderTableName    = 'servicename';

}
