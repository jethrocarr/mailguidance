--
-- MAILGUIDANCE APPLICATION
--
-- Inital database install SQL.
--

CREATE DATABASE `mailguidance` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `mailguidance`;



--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`name`, `value`) VALUES
('APP_MYSQL_DUMP', '/usr/bin/mysqldump'),
('APP_PDFLATEX', '/usr/bin/pdflatex'),
('ARCHIVE_FILTER_FOLDERS', '1'),
('ARCHIVE_INBOX_ALLMAIL', '1'),
('BLACKLIST_ENABLE', 'disabled'),
('BLACKLIST_LIMIT', '10'),
('DATA_STORAGE_LOCATION', 'use_database'),
('DATA_STORAGE_METHOD', 'database'),
('DATEFORMAT', 'yyyy-mm-dd'),
('LANGUAGE_DEFAULT', 'en_us'),
('LANGUAGE_LOAD', 'preload'),
('MAIL_DEFAULT_ADDRESS', 'developers@amberdms.com'),
('MAIL_DEFAULT_MODE', 'everyone'),
('PATH_TMPDIR', '/tmp'),
('PHONE_HOME', 'enabled'),
('PHONE_HOME_TIMER', '1257207684'),
('SCHEMA_VERSION', '20091026'),
('SUBSCRIPTION_ID', '5f4d732e933c8ac621d99c0e2a15a536'),
('SUBSCRIPTION_SUPPORT', 'opensource'),
('TIMEZONE_DEFAULT', 'SYSTEM'),
('UPLOAD_MAXBYTES', '5242880');

-- --------------------------------------------------------

--
-- Table structure for table `filters`
--

CREATE TABLE IF NOT EXISTS `filters` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `type` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;


-- --------------------------------------------------------

--
-- Table structure for table `filters_users`
--

CREATE TABLE IF NOT EXISTS `filters_users` (
  `id` int(11) NOT NULL auto_increment,
  `id_user` int(11) NOT NULL,
  `id_filter` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;


-- --------------------------------------------------------

--
-- Table structure for table `filter_types`
--

CREATE TABLE IF NOT EXISTS `filter_types` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `filter_types`
--

INSERT INTO `filter_types` (`id`, `type`) VALUES
(1, 'sender_domain'),
(2, 'custom'),
(3, 'subject');

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL auto_increment,
  `language` varchar(20) NOT NULL,
  `label` varchar(255) NOT NULL,
  `translation` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `language` (`language`),
  KEY `label` (`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=319 ;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES
(292, 'en_us', 'overview', 'Overview'),
(293, 'en_us', 'filter_title', 'Filter'),
(294, 'en_us', 'filter_filter_search', 'Filter Search'),
(295, 'en_us', 'filter_assignment', 'Filter Assignment'),
(296, 'en_us', 'filter_configuration', 'Filter Configuration'),
(297, 'en_us', 'filter_add', 'Add Filter'),
(298, 'en_us', 'filter_view', 'View Filters'),
(299, 'en_us', 'filter_details', 'Filter Details'),
(300, 'en_us', 'admin_users', 'User Management'),
(301, 'en_us', 'admin_users_view', 'View Users'),
(302, 'en_us', 'admin_users_add', 'Add User'),
(303, 'en_us', 'configuration', 'Configuration'),
(304, 'en_us', 'config_mail_default', 'Unmatched Mail Handling'),
(305, 'en_us', 'config_mail_archive', 'Mail Archiving Options'),
(306, 'en_us', 'config_security', 'Security & Authentication'),
(307, 'en_us', 'config_dateandtime', 'Date & Time'),
(308, 'en_us', 'filter_searchbox', 'Searchbox'),
(309, 'en_us', 'tbl_lnk_details', 'details'),
(310, 'en_us', 'tbl_lnk_permissions', 'permissions'),
(311, 'en_us', 'tbl_lnk_delete', 'delete'),
(312, 'en_us', 'user_permissions', 'User Permissions'),
(313, 'en_us', 'title', 'Title'),
(314, 'en_us', 'type', 'Type'),
(315, 'en_us', 'value', 'Value'),
(316, 'en_us', 'delete_confirm', 'Confirm Deletion'),
(317, 'en_us', 'username_mailguidance', 'Username'),
(318, 'en_us', 'password_mailguidance', 'Password');

-- --------------------------------------------------------

--
-- Table structure for table `language_avaliable`
--

CREATE TABLE IF NOT EXISTS `language_avaliable` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `language_avaliable`
--

INSERT INTO `language_avaliable` (`id`, `name`) VALUES
(1, 'en_us');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL auto_increment,
  `priority` int(11) NOT NULL default '0',
  `parent` varchar(50) NOT NULL,
  `topic` varchar(50) NOT NULL,
  `link` varchar(50) NOT NULL,
  `permid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=185 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES
(170, 100, 'top', 'overview', 'home.php', 0),
(171, 200, 'top', 'filter_assignment', 'assignment/assignment.php', 6),
(172, 300, 'top', 'filter_configuration', 'filters/filters.php', 4),
(173, 311, 'filter_configuration', 'filter_view', 'filters/filters.php', 4),
(174, 310, 'filter_configuration', 'filter_add', 'filters/add.php', 5),
(175, 500, 'top', 'admin_users', 'user/users.php', 2),
(177, 510, 'admin_users', 'admin_users_add', 'user/user-add.php', 2),
(178, 520, 'admin_users', 'admin_users_view', 'user/users.php', 2),
(179, 521, 'admin_users_view', '', 'user/user-view.php', 2),
(180, 522, 'admin_users_view', '', 'user/user-delete.php', 2),
(181, 523, 'admin_users_view', '', 'user/user-permissions.php', 2),
(182, 312, 'filter_view', '', 'filters/view.php', 4),
(183, 313, 'filter_view', '', 'filters/delete.php', 5),
(184, 900, 'top', 'configuration', 'admin/config.php', 2);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL auto_increment,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores all the possible permissions' AUTO_INCREMENT=8 ;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `value`, `description`) VALUES
(1, 'disabled', 'Enabling the disabled permission will prevent the user from being able to login.'),
(2, 'admin', 'Provides access to user and configuration management features (note: any user with admin can provide themselves with access to any other section of this program)'),
(4, 'filters_read', 'Allow user to view filter configuration.'),
(5, 'filters_write', 'Allow user to write filter configuration.'),
(6, 'assignment_read', 'Allow user to read assigment settings.'),
(7, 'assignment_write', 'Allow user to write assignment settings.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `realname` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_salt` varchar(20) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `time` bigint(20) NOT NULL default '0',
  `ipaddress` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ipaddress` (`ipaddress`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='User authentication system.' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `realname`, `password`, `password_salt`, `contact_email`, `time`, `ipaddress`) VALUES
(1, 'setup', 'Setup Account', '14c2a5c3681b95582c3e01fc19f49853d9cdbb31', 'hctw8lbz3uhxl6sj8ixr', 'support@amberdms.com', 0, '127.0.0.1');

-- --------------------------------------------------------

--
-- Table structure for table `users_blacklist`
--

CREATE TABLE IF NOT EXISTS `users_blacklist` (
  `id` int(11) NOT NULL auto_increment,
  `ipaddress` varchar(15) NOT NULL,
  `failedcount` int(11) NOT NULL default '0',
  `time` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Prevents automated login attacks.' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users_blacklist`
--


-- --------------------------------------------------------

--
-- Table structure for table `users_holidaymode`
--

CREATE TABLE IF NOT EXISTS `users_holidaymode` (
  `id` int(11) NOT NULL auto_increment,
  `id_user` int(11) NOT NULL,
  `id_user_redirect` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;



-- --------------------------------------------------------

--
-- Table structure for table `users_options`
--

CREATE TABLE IF NOT EXISTS `users_options` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=310 ;

--
-- Dumping data for table `users_options`
--

INSERT INTO `users_options` (`id`, `userid`, `name`, `value`) VALUES
(304, 1, 'lang', 'en_us'),
(305, 1, 'dateformat', 'yyyy-mm-dd'),
(306, 1, 'shrink_tableoptions', ''),
(307, 1, 'default_employeeid', ''),
(308, 1, 'debug', ''),
(309, 1, 'concurrent_logins', 'on');

-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--

CREATE TABLE IF NOT EXISTS `users_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `permid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user permissions.' AUTO_INCREMENT=10 ;

--
-- Dumping data for table `users_permissions`
--

INSERT INTO `users_permissions` (`id`, `userid`, `permid`) VALUES
(1, 1, 2),
(2, 1, 6),
(3, 1, 4),
(8, 1, 5),
(9, 1, 7);

-- --------------------------------------------------------

--
-- Table structure for table `users_sessions`
--

CREATE TABLE IF NOT EXISTS `users_sessions` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `authkey` varchar(40) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `time` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;





--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20091026' WHERE name='SCHEMA_VERSION' LIMIT 1;




--
-- Database Installation Complete
--



---
--- Upgrade 20120506
---

UPDATE `config` SET `value` = 'disabled' WHERE `config`.`name` = 'PHONE_HOME';

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'username', 'Username');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'realname', 'Real Name');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'contact_email', 'Email Address');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'lastlogin_time', 'Last Login Time');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'user_view', 'User Details');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'user_holiday', 'Holiday Mode');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'user_password', 'Password & Credentials');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'user_info', 'User information');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'user_options', 'User Options');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'submit', 'Save Changes');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'lastlogin_ipaddress', 'Last IP Address');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'holiday_mode', 'Holiday Mode');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'option_lang', 'Preferred Language');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'option_dateformat', 'Preferred Date Format');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'option_shrink_tableoptions', 'Hide option tables by default');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'option_debug', 'Enable Debugging');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password', 'Password');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password_confirm', 'Password (confirm)');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'user_delete', 'Delete User');

UPDATE `config` SET `value` = '20120506' WHERE `config`.`name` = 'SCHEMA_VERSION';



