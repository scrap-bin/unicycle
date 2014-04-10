DROP TABLE IF EXISTS `:p_users`;

CREATE TABLE `:p_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(200) NOT NULL,
  `realname` varchar(200) NOT NULL,
  `created` timestamp NOT NULL,
  `updated` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8;

DROP TABLE IF EXISTS `:p_user_socials`;

CREATE TABLE `:p_user_socials` (
  `user_id` int(10) NOT NULL,
  `provider` varchar(200) NOT NULL,
  `provider_uid` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `display_name` varchar(200) NOT NULL,
  `first_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `profile_url` varchar(300) NOT NULL,
  `photo_url` varchar(300) NOT NULL,
  `created` timestamp NOT NULL,
  PRIMARY KEY (`user_id`, `provider`),
  KEY (`provider`, `provider_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

