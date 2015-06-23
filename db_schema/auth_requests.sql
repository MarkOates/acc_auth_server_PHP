
CREATE TABLE IF NOT EXISTS `auth_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `auth_key` varchar(32) NOT NULL,
  `requested_by_ip` varchar(256) NOT NULL,
  `requested_by_user_agent` varchar(256) NOT NULL,
  `notify_url` char(128) NOT NULL,
  `datetime_validated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `validated_by_user_id` int(11) NOT NULL,
  `shared_id_key` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
