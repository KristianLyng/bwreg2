-- MySQL dump 9.11
--
-- Host: localhost    Database: bwreg2
-- ------------------------------------------------------
-- Server version	4.0.24_Debian-10sarge2-log

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `id` int(11) NOT NULL auto_increment,
  `contentid` int(11) NOT NULL default '0',
  `version` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `modified` timestamp(14) NOT NULL,
  `gid` int(11) NOT NULL default '0',
  `permission` int(11) default NULL,
  `content` text,
  `title` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `content`
--

INSERT INTO `content` VALUES (5,3,1,1,20070313000415,1,NULL,'+ Fin oversikt\n  \nOm man vil, kan man linke til forsiden sin slik: FrontPage \nEller bedre: [FrontPage Forsiden]\n','HitEn');
INSERT INTO `content` VALUES (4,2,1,1,20070312235947,1,NULL,'+ Dette er en annen side\n\n\nBlir dette masse linjeskift?\n\nTilbake til [Forsiden] ?\n\nEventuelt [http://glug.grm.hia.no/~kristian/template.php?page=BølerLAN Forsiden]\n\nTil og med BølerLAN ?\n\n\n\nhmm...\n','DetteHer');
INSERT INTO `content` VALUES (3,1,2,1,20070312235325,1,NULL,'+ Overskiften paa forsiden\n\nDette er vanlig tekst, **dette er ganske fett**\nVi kan skrive ganske firtt\nfritt til og med....\n\n++ Demo av lenker \n\n//lenker// er vistnok enkelt. Er DetteHer en lenke?\nHva med [HitEn Dette her da] ?\n','BølerLAN');

--
-- Table structure for table `events`
--

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
) TYPE=MyISAM;

--
-- Dumping data for table `events`
--

INSERT INTO `events` VALUES (1,1,'BølerLAN','images/bolerlanlogo.png','BølerLAN er tidenes beste LAN party',NULL,1,NULL,NULL,NULL,'blan');
INSERT INTO `events` VALUES (2,2,'JallaLAN','images/glider.png',NULL,NULL,NULL,NULL,NULL,NULL,'jallalan');

--
-- Table structure for table `location`
--

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
) TYPE=MyISAM;

--
-- Dumping data for table `location`
--

INSERT INTO `location` VALUES (1,'Skullerud Skole','SkullerudSkoleAddresse','Gaa av paa skullerud T','<img src=\"images/skullerud1.png\" />',8,12,96,'Scene / Crew','Inngang','Kantine/Jentegardrobe','Guttegardrobe/Sovesal');

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

--
-- Dumping data for table `users`
--

INSERT INTO `users` VALUES (1,'kristian','Kristian','Lyngstol','kristianlyng@gmail.com',1983,'Langretta 10','99014497','Admin/crew/whatever','hei');
INSERT INTO `users` VALUES (2,'kristian2','Kristoffer','asfyngstol','kristianlyng@gmail.com',1983,'Langretta 10','99014497','Admin/crew/whatever',NULL);

