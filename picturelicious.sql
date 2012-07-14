CREATE TABLE `pl_comments` (
  `id` int(11) NOT NULL auto_increment,
  `imageId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `content` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `imageId` (`imageId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `pl_imagecolors` (
  `id` int(11) NOT NULL auto_increment,
  `imageId` int(11) NOT NULL,
  `r` tinyint(3) unsigned NOT NULL default '0',
  `g` tinyint(3) unsigned NOT NULL default '0',
  `b` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `pl_images` (
  `id` int(11) NOT NULL auto_increment,
  `logged` datetime NOT NULL,
  `user` int(11) NOT NULL,
  `score` float NOT NULL default '0',
  `votes` int(11) NOT NULL default '0',
  `keyword` varchar(255) collate utf8_unicode_ci NOT NULL,
  `tags` text collate utf8_unicode_ci NOT NULL,
  `image` varchar(255) collate utf8_unicode_ci NOT NULL,
  `thumb` varchar(255) collate utf8_unicode_ci NOT NULL,
  `hash` char(32) collate utf8_unicode_ci NOT NULL,
  `source` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `keyword` (`keyword`),
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `pl_iplock` (
  `ip` int(11) NOT NULL,
  `imageId` int(11) NOT NULL,
  `ts` int(11) NOT NULL,
  PRIMARY KEY  (`ip`,`imageId`),
  KEY `ts` (`ts`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `pl_taglog` (
  `id` int(11) NOT NULL auto_increment,
  `tagged` datetime NOT NULL,
  `userId` int(11) NOT NULL,
  `imageId` int(11) NOT NULL,
  `locked` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tagged` (`tagged`),
  KEY `userId` (`userId`),
  KEY `imageId` (`imageId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `pl_uploadlock` (
  `id` int(11) NOT NULL auto_increment,
  `ip` int(11) NOT NULL,
  `ts` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `pl_users` (
  `id` int(11) NOT NULL auto_increment,
  `registered` datetime NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `pass` char(32) collate utf8_unicode_ci NOT NULL,
  `valid` tinyint(4) NOT NULL,
  `remember` char(32) collate utf8_unicode_ci NOT NULL,
  `admin` tinyint(4) NOT NULL default '0',
  `score` int(11) NOT NULL,
  `images` int(11) NOT NULL default '0',
  `avatar` varchar(255) collate utf8_unicode_ci NOT NULL,
  `website` varchar(255) collate utf8_unicode_ci NOT NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
