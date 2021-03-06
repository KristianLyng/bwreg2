-- MySQL dump 10.11
--
-- Host: localhost    Database: bwreg2
-- ------------------------------------------------------
-- Server version	5.0.38-Ubuntu_0ubuntu1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `contentid` int(11) NOT NULL default '0',
  `version` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `gid` int(11) NOT NULL default '0',
  `permission` int(11) default NULL,
  `content` text,
  `title` varchar(50) NOT NULL default '',
  KEY `contentindex` (`contentid`,`version`,`gid`),
  KEY `content_permission_Idx` (`permission`),
  CONSTRAINT `fk_content_permissions` FOREIGN KEY (`permission`) REFERENCES `permissions` (`resource`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  PRIMARY KEY  (`eid`),
  KEY `events_gid_Idx` (`gid`),
  KEY `events_location_Idx` (`location`),
  CONSTRAINT `fk_events_location` FOREIGN KEY (`location`) REFERENCES `location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
CREATE TABLE `group_members` (
  `groupid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `level` int(11) default NULL,
  `role` varchar(20) default NULL,
  KEY `group_members_groupid_Idx` (`groupid`),
  KEY `group_members_uid_Idx` (`uid`),
  CONSTRAINT `fk_group_members_users` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`),
  CONSTRAINT `fk_group_members_groups` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `options` varchar(50) default NULL,
  PRIMARY KEY  (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `eid` int(11) default NULL,
  `sname` varchar(10) NOT NULL default '',
  `title` varchar(100) default NULL,
  `uid` int(11) NOT NULL default '0',
  `content` text,
  `date` datetime default NULL,
  `identifier` varchar(100) default NULL,
  `gid` int(11) NOT NULL default '0',
  UNIQUE KEY `news_identifier_gid_Idx` (`identifier`,`gid`),
  KEY `news_sname_Idx` (`sname`),
  KEY `news_uid_Idx` (`uid`),
  CONSTRAINT `fk_news_news_categories` FOREIGN KEY (`sname`) REFERENCES `news_categories` (`sname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `news_categories`
--

DROP TABLE IF EXISTS `news_categories`;
CREATE TABLE `news_categories` (
  `gid` int(11) NOT NULL default '0',
  `permission` int(11) NOT NULL default '0',
  `sname` varchar(10) default NULL,
  `heading` varchar(30) default NULL,
  `description` varchar(100) default NULL,
  KEY `news_categories_gid_Idx` (`gid`),
  KEY `news_categories_permission_Idx` (`permission`),
  KEY `news_categories_sname_Idx` (`sname`),
  CONSTRAINT `fk_news_categories_permissions` FOREIGN KEY (`permission`) REFERENCES `permissions` (`resource`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `groupid` int(11) NOT NULL default '0',
  KEY `permissions_gid_Idx` (`gid`),
  KEY `permissions_groupid_Idx` (`groupid`),
  KEY `permissions_resource_Idx` (`resource`),
  KEY `permissions_resource_name_Idx` (`resource_name`),
  CONSTRAINT `fk_permissions_events` FOREIGN KEY (`gid`) REFERENCES `events` (`gid`),
  CONSTRAINT `fk_permissions_groups` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `ticket_state`
--

DROP TABLE IF EXISTS `ticket_state`;
CREATE TABLE `ticket_state` (
  `eid` int(11) NOT NULL,
  `force_state` enum('Enabled','Disabled','None') default NULL,
  `period_start` datetime default NULL,
  `period_end` datetime default NULL,
  `option_queue` tinyint(1) default NULL,
  `seating_start` datetime default NULL,
  `seating_end` datetime default NULL,
  `seating_group_start` datetime default NULL,
  `seating_group_end` datetime default NULL,
  `seating_group_size` int(11) default NULL,
  `tickets` int(11) default NULL,
  PRIMARY KEY  (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `eid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL auto_increment,
  `state` enum('queue','ordered','payed','canceled-not-payed','canceled-payed','canceled-refunded') default NULL,
  `arrived` tinyint(1) default NULL,
  `seat` int(11) default NULL,
  `seater` int(11) default NULL,
  PRIMARY KEY  (`ticket_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

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
  `private` varchar(20) default NULL,
  `css` varchar(50) default NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-09-09 21:41:50
