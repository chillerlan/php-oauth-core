<?php
/**
 * @filesource   dbstorage_create.php
 * @created      23.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\Database\{
	Database,
	DatabaseOptions,
	Drivers\MySQLiDrv,
};
use chillerlan\Traits\DotEnv;

require_once __DIR__.'/../vendor/autoload.php';

const CFGDIR         = __DIR__.'/../config';
const TABLE_TOKEN    = 'storagetest';
const TABLE_PROVIDER = 'storagetest_providers';

$providers = [
	1 => 'Discogs',
	2 => 'Twitter',
	3 => 'Flickr',
	4 => 'Foursquare',
	5 => 'GitHub',
	6 => 'Gitter',
	7 => 'Google',
	8 => 'Instagram',
	9 => 'MusicBrainz',
	10 => 'SoundCloud',
	11 => 'Discord',
	12 => 'Spotify',
	13 => 'Twitch',
	14 => 'Vimeo',
	15 => 'LastFM',
	16 => 'GuildWars2',
	17 => 'Tumblr',
	18 => 'Patreon',
	19 => 'Twitter2',
	20 => 'Wordpress',
	21 => 'DeviantArt',
	22 => 'YahooSocial',
	23 => 'Deezer',
	24 => 'Mixcloud',
	25 => 'Slack',
	26 => 'Amazon',
	27 => 'BigCartel',
	28 => 'Stripe',
];

$env = (new DotEnv(CFGDIR, file_exists(CFGDIR.'/.env') ? '.env' : '.env_travis'))->load();

$db = new Database(new DatabaseOptions([
	'driver'       => MySQLiDrv::class,
	'host'         => $env->get('MYSQL_HOST'),
	'port'         => $env->get('MYSQL_PORT'),
	'database'     => $env->get('MYSQL_DATABASE'),
	'username'     => $env->get('MYSQL_USERNAME'),
	'password'     => $env->get('MYSQL_PASSWORD'),
]));

$db->connect();

$db->raw('DROP TABLE IF EXISTS '.TABLE_TOKEN);
$db->create
	->table(TABLE_TOKEN)
	->primaryKey('label')
	->varchar('label', 32, null, false)
	->int('user_id',10, null, false)
	->varchar('provider_id', 30, '', false)
	->text('token', null, true)
	->text('state')
	->int('expires',10, null, false)
	->query();

$db->raw('DROP TABLE IF EXISTS '.TABLE_PROVIDER);
$db->create
	->table(TABLE_PROVIDER)
	->primaryKey('provider_id')
	->tinyint('provider_id',10, null, false, 'UNSIGNED AUTO_INCREMENT')
	->varchar('servicename', 30, '', false)
	->query();

foreach($providers as $i => $provider){
	$db->insert
		->into(TABLE_PROVIDER)
		->values(['provider_id' => $i, 'servicename' => $provider])
		->query();
}

echo PHP_EOL.'created tables: "'.TABLE_TOKEN.'" and "'.TABLE_PROVIDER.'"'.PHP_EOL;

exit;
