# Basic Syntax:
# GRANT SELECT,UPDATE,DELETE,INSERT ON [db name].* TO 'login'@[host] IDENTIFIED  BY 'password';
# CREATE DATABASE [your cp database name];
# USE [your cp database name];

# If you want another login, change 'cp' to 'your new_login'
# Be sure to change default_password to the password that you defined in config.php
# If you are running the CP on a webhost, and hosting SQL yourself, make sure that you change 'cp'@localhost to
# 'cp'@host_ip

GRANT SELECT,UPDATE,DELETE,INSERT ON ragnarok.* TO 'cp'@localhost IDENTIFIED BY 'cp';
GRANT SELECT,UPDATE,DELETE,INSERT ON cp.* TO 'cp'@localhost IDENTIFIED BY 'cp';

CREATE DATABASE cp;
USE cp;

CREATE TABLE `access_log` (
  `action_id` int(11) NOT NULL auto_increment,
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `User/IP` text NOT NULL,
  `Action` text NOT NULL,
  PRIMARY KEY  (`action_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `admin_announce` (
  `post_id` int(11) NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  `poster` text NOT NULL,
  PRIMARY KEY  (`post_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `admin_log` (
  `action_id` int(11) NOT NULL auto_increment,
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `User` text NOT NULL,
  `Action` text NOT NULL,
  PRIMARY KEY  (`action_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `anti_bot` (
  `reg_id` text NOT NULL,
  `reg_code` int(11) NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE `ban_log` (
  `action_id` int(11) NOT NULL auto_increment,
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `set_account_id` text NOT NULL,
  `ban_account_id` text NOT NULL,
  `reason` text NOT NULL,
  PRIMARY KEY  (`action_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `exploit_log` (
  `action_id` int(11) NOT NULL auto_increment,
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `User/IP` text NOT NULL,
  `Action` text NOT NULL,
  PRIMARY KEY  (`action_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `gm_announce` (
  `post_id` int(11) NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  `poster` text NOT NULL,
  PRIMARY KEY  (`post_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `ladder_ignore` (
  `account_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`account_id`)
) TYPE=MyISAM;

CREATE TABLE `whosonline_ignore` (
  `account_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`account_id`)
) TYPE=MyISAM;

CREATE TABLE `money_log` (
  `action_id` int(11) NOT NULL auto_increment,
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `From` int(11) NOT NULL default '0',
  `To` int(11) NOT NULL default '0',
  `Action` text NOT NULL,
  PRIMARY KEY  (`action_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `pending` (
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `auth_code` varchar(32) NOT NULL default '',
  `userid` varchar(24) NOT NULL default '',
  `user_pass` varchar(32) NOT NULL default '',
  `gender` char(1) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `ip` varchar(15) NOT NULL
) TYPE=MyISAM;

CREATE TABLE `privilege` (
  `account_id` int(11) NOT NULL default '0',
  `privilege` int(11) NOT NULL default '0',
  PRIMARY KEY  (`account_id`)
) TYPE=MyISAM;

CREATE TABLE `query_log` (
  `action_id` int(11) NOT NULL auto_increment,
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `User` varchar(24) NOT NULL default '',
  `IP` varchar(20) NOT NULL default '',
  `page` varchar(24) NOT NULL default '',
  `query` text NOT NULL,
  PRIMARY KEY  (`action_id`),
  KEY `action_id` (`action_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `register_log` (
  `reg_id` int(11) NOT NULL auto_increment,
  `account_name` text NOT NULL,
  `ip` int(11) NOT NULL default '0',
  `reg_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `Email` varchar(60) NOT NULL default '',
  `level` smallint(3) NOT NULL default '0',
  PRIMARY KEY  (`reg_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `skins` (
  `login` text NOT NULL,
  `skin` text NOT NULL
) TYPE=MyISAM;

CREATE TABLE `status` (
  `last_checked` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_serv` tinyint(1) NOT NULL default '0',
  `char_serv` tinyint(1) NOT NULL default '0',
  `map_serv` tinyint(1) NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE `user_announce` (
  `post_id` int(11) NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  `poster` text NOT NULL,
  PRIMARY KEY  (`post_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `user_log` (
  `action_id` int(11) NOT NULL auto_increment,
  `Date` datetime NOT NULL default '0000-00-00 00:00:00',
  `User` text NOT NULL,
  `Action` text NOT NULL,
  PRIMARY KEY  (`action_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;