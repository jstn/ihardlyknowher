CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nsid` varchar(255) collate utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `token` varchar(255) collate utf8_unicode_ci default NULL,
  `urlname` varchar(255) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
)

CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nsid` varchar(255) NOT NULL,
  `modified` datetime NOT NULL,
  `background` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nsid` (`nsid`)
)