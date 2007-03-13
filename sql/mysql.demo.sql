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
  `title` varchar(50) NOT NULL default '',
  `read_permission` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `content`
--

INSERT INTO `content` VALUES (5,3,1,1,20070312230415,1,NULL,'+ Fin oversikt\n  \nOm man vil, kan man linke til forsiden sin slik: FrontPage \nEller bedre: [FrontPage Forsiden]\n','HitEn',NULL);
INSERT INTO `content` VALUES (4,2,1,1,20070312225947,1,NULL,'+ Dette er en annen side\n\n\nBlir dette masse linjeskift?\n\nTilbake til [Forsiden] ?\n\nEventuelt [http://glug.grm.hia.no/~kristian/template.php?page=BølerLAN Forsiden]\n\nTil og med BølerLAN ?\n\n\n\nhmm...\n','DetteHer',NULL);
INSERT INTO `content` VALUES (3,1,2,1,20070312225325,1,NULL,'+ Overskiften paa forsiden\n\nDette er vanlig tekst, **dette er ganske fett**\nVi kan skrive ganske firtt\nfritt til og med....\n\n++ Demo av lenker \n\n//lenker// er vistnok enkelt. Er DetteHer en lenke?\nHva med [HitEn Dette her da] ?\n','BølerLAN',NULL);
INSERT INTO `content` VALUES (6,4,2,1,20070312234329,1,NULL,'+ SubWikier\nLink til brukere: user:Kristian \nLink til news : news:PetterEierDeg\n','DetteHer',NULL);
INSERT INTO `content` VALUES (7,4,2,1,20070312234636,1,NULL,'+ !SubWikier\nLink til brukere: user:Kristian \nLink til news : news:PetterEierDeg\n[FrontPage Tilbake til forsiden]\n','DetteHer',NULL);
INSERT INTO `content` VALUES (8,4,3,1,20070313140355,1,1,'+ !SubWikier\nLink til brukere: user:Kristian \nLink til news : news:PetterEierDeg\n[FrontPage Tilbake til forsiden]\n','DetteHer',NULL);
INSERT INTO `content` VALUES (9,5,1,1,20070313162725,1,NULL,'+ Denne siden finnes ikke\nSiden du forsoeker og naa eksisterer ikke, dette skal ikke skje.\n\n [FrontPage Tilbake til forsiden]\n','ErrorPageNotFound',NULL);
INSERT INTO `content` VALUES (12,6,1,1,20070313163149,1,NULL,'++ Du kan lage denne siden!\nBidra til at ting fungerer...','ErrorPageNotFoundAdmin',1);

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
  `css` varchar(50) default 'default.css',
  PRIMARY KEY  (`eid`)
) TYPE=MyISAM;

--
-- Dumping data for table `events`
--

INSERT INTO `events` VALUES (1,1,'BølerLAN','images/bolerlanlogo.png','BølerLAN er tidenes beste LAN party',NULL,1,NULL,NULL,NULL,'blan','default.css');
INSERT INTO `events` VALUES (2,2,'JallaLAN','images/glider.png',NULL,NULL,NULL,NULL,NULL,NULL,'jallalan','css/default.css');

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `groupid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `level` int(11) default NULL,
  `role` varchar(20) default NULL
) TYPE=MyISAM;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` VALUES (1,1,10,'Developer');
INSERT INTO `group_members` VALUES (2,1,10,'Developer');

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `groupid` int(11) NOT NULL auto_increment,
  `gid` int(11) default NULL,
  `eid` int(11) default NULL,
  `group_name` varchar(20) default NULL,
  `group_description` text,
  `owner` int(11) NOT NULL default '0',
  PRIMARY KEY  (`groupid`)
) TYPE=MyISAM;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` VALUES (1,0,0,'BWReg Admins','The superusers of BWReg',1);
INSERT INTO `groups` VALUES (2,1,0,'BolerLAN Admins','The superusers of BolerLAN',1);

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
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `gid` int(11) NOT NULL default '0',
  `eid` int(11) default NULL,
  `resource` int(11) default NULL,
  `resource_name` varchar(20) default NULL,
  `permissions` varchar(5) default NULL,
  `groupid` int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` VALUES (0,0,1,'BWReg2','rwm',1);
INSERT INTO `permissions` VALUES (1,0,2,'blanContentCreators','rwm',1);

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

