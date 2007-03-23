-- MySQL dump 10.11
--
-- Host: localhost    Database: bwreg2
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_10-log
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,MYSQL40' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `contentid` int(11) NOT NULL auto_increment,
  `version` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `modified` timestamp NOT NULL,
  `gid` int(11) NOT NULL default '0',
  `permission` int(11) default NULL,
  `content` text,
  `title` varchar(50) NOT NULL default '',
  UNIQUE KEY `contentindex` (`contentid`,`version`,`gid`)
) TYPE=MyISAM AUTO_INCREMENT=36;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `eid` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `title` varchar(20) default NULL,
  `logo` varchar(50) default NULL,
  `description` varchar(100) default NULL,
  `price` varchar(150) default NULL,
  `location` int(11) default NULL,
  `payment` varchar(150) default NULL,
  `start` datetime default NULL,
  `end` datetime default NULL,
  `gname` varchar(10) default NULL,
  `css` varchar(50) default 'default.css',
  PRIMARY KEY  (`eid`)
) TYPE=MyISAM;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
CREATE TABLE `group_members` (
  `groupid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `level` int(11) default NULL,
  `role` varchar(20) default NULL
) TYPE=MyISAM;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `groupid` int(11) NOT NULL auto_increment,
  `gid` int(11) default NULL,
  `group_name` varchar(20) default NULL,
  `group_description` text,
  `owner` int(11) NOT NULL default '0',
  PRIMARY KEY  (`groupid`)
) TYPE=MyISAM AUTO_INCREMENT=7;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `location` int(11) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `address` varchar(200) default NULL,
  `directions` text,
  `maplink` varchar(200) default NULL,
  `rows` int(11) default NULL,
  `cols` int(11) default NULL,
  `seats` int(11) default NULL,
  `north` varchar(50) default NULL,
  `south` varchar(50) default NULL,
  `east` varchar(50) default NULL,
  `west` varchar(50) default NULL,
  PRIMARY KEY  (`location`)
) TYPE=MyISAM AUTO_INCREMENT=2;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `gid` int(11) NOT NULL default '0',
  `eid` int(11) default NULL,
  `resource` int(11) default NULL,
  `resource_name` varchar(50) default NULL,
  `permissions` varchar(5) default NULL,
  `groupid` int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
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
) TYPE=MyISAM AUTO_INCREMENT=6;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-03-23 12:04:42
