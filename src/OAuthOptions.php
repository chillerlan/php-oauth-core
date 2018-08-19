<?php
/**
 * Class OAuthOptions
 *
 * @filesource   OAuthOptions.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth;

use chillerlan\HTTP\HTTPOptionsTrait;
use chillerlan\Traits\ImmutableSettingsAbstract;

/**
 * OAuthOptionsTrait
 *
 * @property string     $key
 * @property string     $secret
 * @property string     $callbackURL
 * @property bool       $sandboxMode
 * @property bool       $sessionStart
 * @property string     $sessionTokenVar
 * @property string     $sessionStateVar
 * @property bool       $tokenAutoRefresh
 *
 *
 * HTTPOptionsTrait
 *
 * @property string     $user_agent
 * @property int        $timeout
 * @property array      $curl_options
 * @property string     $ca_info
 * @property int        $max_redirects
 */
class OAuthOptions extends ImmutableSettingsAbstract{
	use OAuthOptionsTrait, HTTPOptionsTrait;
}
