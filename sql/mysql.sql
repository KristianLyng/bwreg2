-- MySQL dump 9.11
--
-- Host: localhost    Database: bwreg2
-- ------------------------------------------------------
-- Server version	4.0.24_Debian-10sarge2-log

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(11) NOT NULL auto_increment,
  `uname` varchar(12) NOT NULL default '',
  `firstname` varchar(40) NOT NULL default '',
  `lastname` varchar(40) NOT NULL default '',
  `mail` varchar(40) NOT NULL default '',
  `birthyear` int(11) NOT NULL default '0',
  `adress` varchar(40) NOT NULL default '',
  `phone` varchar(12) NOT NULL default '',
  `extra` varchar(50) default NULL,
  `pass` varchar(20) default NULL,
  PRIMARY KEY  (`uid`)
) TYPE=MyISAM;

