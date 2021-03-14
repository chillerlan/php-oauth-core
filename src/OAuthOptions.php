<?php
/**
 * Class OAuthOptions
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth;

use chillerlan\HTTP\HTTPOptionsTrait;
use chillerlan\Settings\SettingsContainerAbstract;

/**
 * This class holds all settings related to the OAuth provider as well as the default HTTP client.
 *
 * OAuthOptionsTrait
 *
 * @property string     $key
 * @property string     $secret
 * @property string     $callbackURL
 * @property bool       $sessionStart
 * @property string     $sessionTokenVar
 * @property string     $sessionStateVar
 * @property bool       $tokenAutoRefresh
 *
 *
 * HTTPOptionsTrait
 *
 * @property string     $user_agent
 * @property array      $curl_options
 * @property string     $ca_info
 * @property bool       $ssl_verifypeer
 * @property string     $curlHandle
 * @property int        $windowSize
 * @property int|float  $sleep
 * @property int        $timeout
 * @property int        $retries
 * @property array      $curl_multi_options
 */
class OAuthOptions extends SettingsContainerAbstract{
	use OAuthOptionsTrait, HTTPOptionsTrait;
}
