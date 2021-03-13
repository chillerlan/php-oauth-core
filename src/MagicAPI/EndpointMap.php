<?php
/**
 * Class EndpointMap
 *
 * @created      01.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\MagicAPI;

use chillerlan\Settings\SettingsContainerAbstract;

abstract class EndpointMap extends SettingsContainerAbstract implements EndpointMapInterface{

	protected string $API_BASE = '';

}
