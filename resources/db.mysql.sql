CREATE TABLE `storagetest` (
	`label` varchar(32) COLLATE utf8mb4_bin NOT NULL,
	`user_id` int(10) NOT NULL,
	`provider_id` varchar(30) COLLATE utf8mb4_bin NOT NULL,
	`token` text COLLATE utf8mb4_bin,
	`state` text COLLATE utf8mb4_bin,
	`expires` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


CREATE TABLE `storagetest_providers` (
	`provider_id` tinyint(10) UNSIGNED NOT NULL,
	`servicename` varchar(30) COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO `storagetest_providers` (`provider_id`, `servicename`) VALUES
	(1, 'Discogs'),
	(2, 'Twitter'),
	(3, 'Flickr'),
	(4, 'Foursquare'),
	(5, 'GitHub'),
	(6, 'Gitter'),
	(7, 'Google'),
	(8, 'Instagram'),
	(9, 'MusicBrainz'),
	(10, 'SoundCloud'),
	(11, 'Discord'),
	(12, 'Spotify'),
	(13, 'Twitch'),
	(14, 'Vimeo'),
	(15, 'LastFM'),
	(16, 'GuildWars2'),
	(17, 'Tumblr'),
	(18, 'Patreon'),
	(19, 'Twitter2'),
	(20, 'Wordpress'),
	(21, 'DeviantArt'),
	(22, 'YahooSocial'),
	(23, 'Deezer'),
	(24, 'Mixcloud'),
	(25, 'Slack'),
	(26, 'Amazon'),
	(27, 'BigCartel'),
	(28, 'Stripe');

ALTER TABLE `storagetest`
	ADD PRIMARY KEY (`label`);

ALTER TABLE `storagetest_providers`
	ADD PRIMARY KEY (`provider_id`);

ALTER TABLE `storagetest_providers`
	MODIFY `provider_id` tinyint(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
