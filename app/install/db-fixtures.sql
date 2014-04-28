INSERT INTO `:p_users` 
(`id`, `username`, `password`,       `group_id`, `email`,                  `realname`, `language`, `created`,            `updated`) VALUES
(1, 'guest',       'guest',          3,          'guest',                  '',         'English', '2014-03-01 12:00:00', '2014-03-01 12:00:00'),
(2, 'admin',       sha1('password'), 1,          'admin@example.com',      '',         'English', '2014-03-01 12:15:00', '2014-03-01 12:15:00'),
(3, 'artoodetoo',  sha1('password'), 4,          'artoodetoo@example.com', 'R2-D2',    'English', '2014-03-01 12:25:00', '2014-03-01 12:30:00');

INSERT INTO `:p_user_socials`
(`user_id`, `provider`, `provider_uid`, `email`, `display_name`, `first_name`, `last_name`, `profile_url`, `photo_url`, `created`) VALUES
(3, 'facebook', '12345', 'artoodetoo@example.com', 'Artoodetoo', 'R2', 'D2', 'https://www.facebook.com/artoodetoo', 'https://www.facebook.com/artoodetoo.jpg', '2014-03-01 17:55:00');

