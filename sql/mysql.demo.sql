-- MySQL dump 10.11
--
-- Host: localhost    Database: bwreg2
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7-log
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
  `id` int(11) NOT NULL auto_increment,
  `contentid` int(11) NOT NULL default '0',
  `version` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `modified` timestamp NOT NULL,
  `gid` int(11) NOT NULL default '0',
  `permission` int(11) default NULL,
  `content` text,
  `title` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=9;

--
-- Dumping data for table `content`
--

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
INSERT INTO `content` VALUES (5,3,1,1,'2007-03-12 23:04:15',1,NULL,'+ Fin oversikt\n  \nOm man vil, kan man linke til forsiden sin slik: FrontPage \nEller bedre: [FrontPage Forsiden]\n','HitEn'),(4,2,1,1,'2007-03-12 22:59:47',1,NULL,'+ Dette er en annen side\n\n\nBlir dette masse linjeskift?\n\nTilbake til [Forsiden] ?\n\nEventuelt [http://glug.grm.hia.no/~kristian/template.php?page=BølerLAN Forsiden]\n\nTil og med BølerLAN ?\n\n\n\nhmm...\n','DetteHer'),(3,1,2,1,'2007-03-12 22:53:25',1,NULL,'+ Overskiften paa forsiden\n\nDette er vanlig tekst, **dette er ganske fett**\nVi kan skrive ganske firtt\nfritt til og med....\n\n++ Demo av lenker \n\n//lenker// er vistnok enkelt. Er DetteHer en lenke?\nHva med [HitEn Dette her da] ?\n','BølerLAN'),(6,4,2,1,'2007-03-12 23:43:29',1,NULL,'+ SubWikier\nLink til brukere: user:Kristian \nLink til news : news:PetterEierDeg\n','DetteHer'),(7,4,2,1,'2007-03-12 23:46:36',1,NULL,'+ !SubWikier\nLink til brukere: user:Kristian \nLink til news : news:PetterEierDeg\n[FrontPage Tilbake til forsiden]\n','DetteHer'),(8,4,3,1,'2007-03-12 23:47:16',1,NULL,'+ !SubWikier\nLink til brukere: user:Kristian \nLink til news : news:PetterEierDeg\n[FrontPage Tilbake til forsiden]\n','DetteHer');
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `eid` int(11) NOT NULL auto_increment,
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
  PRIMARY KEY  (`eid`)
) TYPE=MyISAM AUTO_INCREMENT=3;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,1,'BølerLAN','images/bolerlanlogo.png','BølerLAN er tidenes beste LAN party',NULL,1,NULL,NULL,NULL,'blan'),(2,2,'JallaLAN','images/glider.png',NULL,NULL,NULL,NULL,NULL,NULL,'jallalan');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
CREATE TABLE `group_members` (
  `groupid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `level` int(11) default NULL,
  `role` varchar(20) default NULL
) TYPE=MyISAM;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
/*!40000 ALTER TABLE `group_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `groupid` int(11) NOT NULL auto_increment,
  `gid` int(11) default NULL,
  `eid` int(11) default NULL,
  `group_name` varchar(20) default NULL,
  `group_description` text,
  `owner` int(11) NOT NULL,
  PRIMARY KEY  (`groupid`)
) TYPE=MyISAM;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` VALUES (1,'Skullerud Skole','SkullerudSkoleAddresse','Gaa av paa skullerud T','<img src=\"images/skullerud1.png\" />',8,12,96,'Scene / Crew','Inngang','Kantine/Jentegardrobe','Guttegardrobe/Sovesal');
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `gid` int(11) NOT NULL,
  `eid` int(11) default NULL,
  `resource` int(11) default NULL,
  `resource_name` varchar(20) default NULL,
  `permissions` varchar(5) default NULL,
  `groupid` int(11) NOT NULL
) TYPE=MyISAM;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

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
) TYPE=MyISAM AUTO_INCREMENT=3;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'kristian','Kristian','Lyngstol','kristianlyng@gmail.com',1983,'Langretta 10','99014497','Admin/crew/whatever','hei'),(2,'kristian2','Kristoffer','asfyngstol','kristianlyng@gmail.com',1983,'Langretta 10','99014497','Admin/crew/whatever',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-03-13 10:38:52
