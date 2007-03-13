-- MySQL dump 9.11
--
-- Host: localhost    Database: bwreg2
-- ------------------------------------------------------
-- Server version	4.0.24_Debian-10sarge2-log

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `contentid` int(11) NOT NULL auto_increment,
  `version` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `modified` timestamp(14) NOT NULL,
  `gid` int(11) NOT NULL default '0',
  `permission` int(11) default NULL,
  `content` text,
  `title` varchar(50) NOT NULL default '',
  `read_permission` int(11) default NULL,
  UNIQUE KEY `contentindex` (`contentid`,`version`,`gid`)
) TYPE=MyISAM;

--
-- Dumping data for table `content`
--

INSERT INTO `content` VALUES (3,1,1,20070313170155,1,2,'+ Fin oversikt\n  \nOm man vil, kan man linke til forsiden sin slik: FrontPage \nEller bedre: [FrontPage Forsiden]\n','HitEn',NULL);
INSERT INTO `content` VALUES (1,2,1,20070313170155,1,2,'+ Overskiften paa forsiden\n\nDette er vanlig tekst, **dette er ganske fett**\nVi kan skrive ganske firtt\nfritt til og med....\n\n++ Demo av lenker \n\n//lenker// er vistnok enkelt. Er DetteHer en lenke?\nHva med [HitEn Dette her da] ?\n','BølerLAN',NULL);
INSERT INTO `content` VALUES (4,2,1,20070313170155,1,2,'+ !SubWikier\nLink til brukere: user:Kristian \nLink til news : news:PetterEierDeg\n[FrontPage Tilbake til forsiden]\n','DetteHer',NULL);
INSERT INTO `content` VALUES (4,3,1,20070313170155,1,2,'+ !SubWikier\nLink til brukere: user:Kristian \nLink til news : news:PetterEierDeg\n[FrontPage Tilbake til forsiden]\n','DetteHer',NULL);
INSERT INTO `content` VALUES (5,1,1,20070313170155,1,2,'+ Denne siden finnes ikke\nSiden du forsoeker og naa eksisterer ikke, dette skal ikke skje.\n\n [FrontPage Tilbake til forsiden]\n','ErrorPageNotFound',NULL);
INSERT INTO `content` VALUES (6,1,1,20070313170155,1,2,'++ Du kan lage denne siden!\nBidra til at ting fungerer...','ErrorPageNotFoundAdmin',1);
INSERT INTO `content` VALUES (4,4,0,20070313224847,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\nasfasfasf','DetteHer',NULL);
INSERT INTO `content` VALUES (4,5,0,20070313224903,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!','DetteHer',NULL);
INSERT INTO `content` VALUES (4,6,0,20070313225848,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSS\\\'en...','DetteHer',NULL);
INSERT INTO `content` VALUES (4,7,0,20070313231213,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSS\\\\\\\'en...\r\n\r\n\r\nfnis','DetteHer',NULL);
INSERT INTO `content` VALUES (4,8,0,20070313231637,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSS\\\'en...\r\n\r\n\r\nfnis','DetteHer',NULL);
INSERT INTO `content` VALUES (4,9,0,20070313231915,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSS\\\'en...\r\n\r\n\r\nfnis','DetteHer',NULL);
INSERT INTO `content` VALUES (4,10,0,20070313231933,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSSen...\r\n\r\n\r\nfnis','DetteHer',NULL);
INSERT INTO `content` VALUES (4,11,0,20070313231950,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSS\\\'en...\r\n\r\n\r\nfnis','DetteHer',NULL);
INSERT INTO `content` VALUES (4,12,0,20070313232306,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSS\\\'en...\r\n\r\n\r\nfnis','DetteHer',NULL);
INSERT INTO `content` VALUES (4,13,0,20070313232506,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSS\\\\\\\'en...\r\n\r\n\r\nfnis','DetteHer',NULL);
INSERT INTO `content` VALUES (4,14,0,20070313232713,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\nVi kan editere!\r\n\r\n\r\nIkke alt er perfekt i CSS\'en...\r\n\r\n\r\nfnis','DetteHer',NULL);
INSERT INTO `content` VALUES (4,15,0,20070313232832,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n','DetteHer',NULL);
INSERT INTO `content` VALUES (1,3,0,20070313233401,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\nkristian / hei\r\n\r\nSlå deg løs!\r\n\r\n','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,4,0,20070313233727,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\nkristian / hei\r\n\r\nSlå deg løs!\r\n\r\n','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,5,0,20070313233830,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\nkristian / hei\r\n\r\nSlå deg løs!\r\n\r\n','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,6,0,20070313233956,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\nkristian / hei\r\n\r\nSlå deg løs!\r\n\r\n','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,7,0,20070313234959,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\nkristian / hei\r\n\r\nSlå deg løs!\r\n\r\n','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,8,0,20070314000807,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\nkristian / hei\r\n\r\nSlå deg løs!\r\n\r\nHei','BølerLAN',NULL);
INSERT INTO `content` VALUES (14,1,1,20070314004604,1,2,'asfasfasfasf','News',NULL);
INSERT INTO `content` VALUES (14,2,1,20070314004804,1,2,'Dette er nyheter....','News',NULL);

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
INSERT INTO `permissions` VALUES (1,0,3,'blan','rwm',1);
INSERT INTO `permissions` VALUES (1,0,3,'blan','rwm',2);

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

