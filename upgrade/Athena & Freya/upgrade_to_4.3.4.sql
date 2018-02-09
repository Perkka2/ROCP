-- Run this query in CP database

CREATE TABLE `whosonline_ignore` (
  `account_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`account_id`)
) TYPE=MyISAM;

ALTER TABLE `register_log` ADD `level` smallint(3) NOT NULL default '0';