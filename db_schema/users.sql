
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime_joined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL DEFAULT 'password',
  `acc_user_id` int(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `datetime_joined`, `username`, `password`, `acc_user_id`) VALUES
(1, '2015-06-22 22:12:42', 'alex', 'password', 1146);
