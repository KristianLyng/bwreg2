-- MySQL dump 10.11
--
-- Host: localhost    Database: bwreg2
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch1-log
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
  `title` varchar(50) NOT NULL,
  PRIMARY KEY  (`contentid`),
  UNIQUE KEY `contentindex` (`contentid`,`version`,`gid`),
  KEY `fk_content_events` (`gid`),
  KEY `fk_content_permissions` (`permission`),
  KEY `fk_content_users` (`uid`),
  CONSTRAINT `fk_content_users` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`),
  CONSTRAINT `fk_content_events` FOREIGN KEY (`gid`) REFERENCES `events` (`gid`),
  CONSTRAINT `fk_content_permissions` FOREIGN KEY (`permission`) REFERENCES `permissions` (`resource`)
) TYPE=InnoDB;

--
-- Dumping data for table `content`
--

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY  (`eid`,`gid`),
  UNIQUE KEY `events_gid_eid_Idx` (`gid`,`eid`),
  KEY `events_gname_Idx` (`gname`),
  KEY `fk_events_location` (`location`),
  KEY `fk_events_permissions` (`gname`),
  CONSTRAINT `fk_events_location` FOREIGN KEY (`location`) REFERENCES `location` (`location`),
  CONSTRAINT `fk_events_permissions` FOREIGN KEY (`gname`) REFERENCES `permissions` (`resource_name`)
) TYPE=InnoDB;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,1,'BølerLAN','images/bolerlanlogo.png','BølerLAN er tidenes beste LAN party',NULL,1,NULL,NULL,NULL,'blan','css/default.css'),(2,2,'JallaLAN','images/glider.png',NULL,NULL,NULL,NULL,NULL,NULL,'jallalan','css/default.css');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
CREATE TABLE `group_members` (
  `groupid` int(11) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  `level` int(11) default NULL,
  `role` varchar(20) default NULL,
  PRIMARY KEY  (`groupid`,`uid`),
  KEY `fk_group_members_groups` (`groupid`),
  KEY `group_members_uid_Idx` (`uid`),
  CONSTRAINT `fk_group_members_groups` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`),
  CONSTRAINT `fk_group_members_users` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`)
) TYPE=InnoDB;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
/*!40000 ALTER TABLE `group_members` DISABLE KEYS */;
INSERT INTO `group_members` VALUES (1,1,10,'dev'),(2,5,1,'Crewsjef'),(3,4,1,'Hei'),(3,5,1,'Crewsjef'),(5,0,1,'Any user'),(6,0,1,'Any user'),(7,3,1,''),(7,4,1,''),(7,5,1,'');
/*!40000 ALTER TABLE `group_members` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY  (`groupid`),
  KEY `fk_groups_users` (`owner`),
  CONSTRAINT `fk_groups_users` FOREIGN KEY (`owner`) REFERENCES `users` (`uid`)
) TYPE=InnoDB;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,0,'BWReg Admins','The superusers of BWReg',1,'l'),(2,1,'BolerLAN Admins','The superusers of BolerLAN',1,'l'),(3,1,'BolerLAN Crew','Fellesgruppe for alle i crew',1,'o'),(4,2,'JallaLAN admins','superusers',1,'l'),(5,1,'All','All users (not logged in too)',1,'l'),(6,0,'All','All users (not logged in too)',1,'l'),(7,1,'Testgruppe1','Testgruppe, aapen',1,'o'),(8,1,'Testgruppe2','Testgruppe, moderert',1,'m');
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
) TYPE=InnoDB;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` VALUES (1,'Skullerud Skole','SkullerudSkoleAddresse','Gaa av paa skullerud T','<img src=\"images/skullerud1.png\" />',8,12,96,'Scene / Crew','Inngang','Kantine/Jentegardrobe','Guttegardrobe/Sovesal');
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `importance` int(10) unsigned NOT NULL default '1',
  `short_message` varchar(50) NOT NULL,
  `long_message` varchar(250) NOT NULL,
  KEY `fk_log_users` (`uid`),
  CONSTRAINT `fk_log_users` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`)
) TYPE=InnoDB;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `eid` int(11) default NULL,
  `sname` varchar(10) NOT NULL,
  `title` varchar(100) default NULL,
  `uid` int(11) NOT NULL default '0',
  `content` text,
  `date` datetime default NULL,
  `identifier` varchar(100) NOT NULL default '',
  `gid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`identifier`,`sname`,`gid`),
  UNIQUE KEY `news_identifier_gid_Idx` (`identifier`,`gid`),
  KEY `fk_news_events` (`eid`),
  KEY `fk_news_news_categories` (`sname`),
  KEY `fk_news_users` (`uid`),
  CONSTRAINT `fk_news_news_categories` FOREIGN KEY (`sname`) REFERENCES `news_categories` (`sname`),
  CONSTRAINT `fk_news_users` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`)
) TYPE=InnoDB;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (0,'bwreg2','Nyheter begynner a ta form',1,'Nyhetsinterfacet begynner aa ta form.\nVi kan naa:\n* Sette inn nyheter paa en side ved hjelp av ``$NEWS:sname$`` i content biten\n* Finne fram alle nyheter for en kategori eller for alle kategorier vi kan lese.\n* Vise enkeltnyheter.\n* Bruke news:FirstPostCrewNews f.eks til aa lenke til andre nyheter, basert paa et unikt navn.\n\nVi kan ikke:\n* Faktisk lage nye nyheter uten aa manuelt sette dem inn i databasen\n* Editere nyheter\n* Slette nyheter\n','2007-04-06 20:35:00','BWReg2NyheterEier',1),(0,'crew','Dette er nyheter til ære for crew',1,'+ KNUT ER EN KLUT\nMen det viste vi strengt talt allerede.\n\nUansett, dette er nyheter for crew. A LAGRE og endre nyheter er i skrivende stund ikke paa plass, men framvisning boer vare greit.\n\n... ** wiki ** formatering selvsagt :)','2007-04-06 20:32:16','FirstPostCrewNews',1),(0,'hoved','Nå kan du spesifisere CSS selv!',1,'Om du har en bruker, kan du nå spesifisere hvilken CSS fil din bruker skal benytte!\r\n\r\nDette betyr at du kan lage din egen CSS fil, legge den ut på din egen server, og sette opp BWReg2 til å bruke denne. Slik kan du enkelt eksperimentere med BWReg2 sitt utseende.\r\n\r\nJeg håper dette kan bidra til at noen der ute forbedrer utseende jeg har defineret i http://glug.grm.hia.no/~kristian/bwreg2demo/css/default.css','2007-04-08 05:44:51','NKanDuSpesifisereCSSSelv',1),(0,'hoved','Nyhetsgrensesnittet er i skrivende stund bedre enn det til BWReg1',1,'Det er bare en ting som er igjenn på nyheter, utover litt polering, og det er å kunne dynamisk opprette kategorier. \r\n\r\nI BWReg1 er dette en statisk liste hardkodet. Rettighetene på kategoriene er også et hardkodet mas uten like. I BWReg2 er alt dette dynamisk; Hver nyhetskategori er beskyttet av en ACL, og nyhetskategoriene er lagret i en database. Skal man vise nyheter på en side legger man ganske enkelt til ``$NEWS:kategorikortnavn$`` et sted på innholdssiden, så dukker det opp nyheter, maks 10. Ønsker man flere eller færre enn 10, kan det også spesifiseres: ``$NEWS:kategorikortnavn,3$`` lister bare 3 nyheter, osv. \r\n\r\nNyhetsarkivet viser automatisk kun de tingene du har lov å se, selvsagt.','2007-04-07 16:39:53','NyhetsgrensesnittetErISkrivendeStundBedreEnnDetTilBWReg',1),(0,'hoved','RSS feed for nyheter',1,'Det er nå mulig å få et RSS feed av nyhetene her.\r\n\r\nSlik blir det enkelt for folk å følge med, uten å besøke siden jevnlig.\r\n\r\nNyheter som ikke er tilgjengelig for alle vil ikke vises i RSS feeden ved mindre du er logget inn når browseren henter den, så dette er ikke veldig hjelpsomt for crew-nyheter f.eks, men likevell en kjekk ting. Forvent en sikkelig lenke straks. \r\n\r\nI mellomtiden:\r\n\r\nhttp://glug.grm.hia.no/~kristian/bwreg2demo/template.php?action=RssNews','2007-04-08 02:55:23','RSSFeedForNyheter',1),(0,'crew','Vi kan lage litt nyheter gitt',1,'Fungerer dette greit?\r\n\r\nHåper det :)','2007-04-07 02:49:24','ViKanLageLittNyheterGitt',1);
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_categories`
--

DROP TABLE IF EXISTS `news_categories`;
CREATE TABLE `news_categories` (
  `gid` int(11) NOT NULL default '0',
  `permission` int(11) NOT NULL default '0',
  `sname` varchar(10) NOT NULL default '',
  `heading` varchar(30) default NULL,
  `description` varchar(100) default NULL,
  PRIMARY KEY  (`sname`),
  UNIQUE KEY `news_categories_gid_sname_Idx` (`gid`,`sname`),
  UNIQUE KEY `news_categories_gid_heading_Idx` (`gid`,`heading`),
  KEY `fk_news_categories_events` (`gid`),
  KEY `fk_news_categories_permissions` (`permission`),
  KEY `news_categories_sname_Idx` (`sname`),
  CONSTRAINT `fk_news_categories_permissions` FOREIGN KEY (`permission`) REFERENCES `permissions` (`resource`),
  CONSTRAINT `fk_news_categories_events` FOREIGN KEY (`gid`) REFERENCES `events` (`gid`)
) TYPE=InnoDB;

--
-- Dumping data for table `news_categories`
--

LOCK TABLES `news_categories` WRITE;
/*!40000 ALTER TABLE `news_categories` DISABLE KEYS */;
INSERT INTO `news_categories` VALUES (1,7,'bwreg2','BWReg2 Nyheter','Dette er mest for testing...'),(1,8,'crew','Crew nyheter','Nyheter for crew'),(1,7,'hoved','Hovedsidenyheter','Dette er viktige nyheter');
/*!40000 ALTER TABLE `news_categories` ENABLE KEYS */;
UNLOCK TABLES;

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
  KEY `fk_permissions_events` (`gid`),
  KEY `fk_permissions_groups` (`groupid`,`gid`),
  KEY `permissions_resource_Idx` (`resource`),
  KEY `permissions_resource_name_Idx` (`resource_name`),
  CONSTRAINT `fk_permissions_groups` FOREIGN KEY (`groupid`) REFERENCES `groups` (`groupid`)
) TYPE=InnoDB;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (0,0,1,'BWReg2','rwm',1),(1,0,3,'blan','rwm',2),(1,0,10,'blanKristiansInfo','r',5),(2,0,5,'jallalan','rwm',4),(2,0,6,'jallalanContentCreators','rwm',4),(1,0,10,'blanPetterPan','rwm',7),(1,0,3,'blan','r',3),(1,0,10,'blanKristiansInfo','rwm',1),(1,0,7,'blanInfo','rwm',2),(1,0,7,'blanInfo','rw',3),(1,0,7,'blanInfo','r',5),(1,0,8,'blanCrewInfo','rwm',2),(0,0,9,'bwreg2Info','rwm',1),(0,0,9,'bwreg2Info','r',6),(1,0,8,'blanCrewInfo','rw',3);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `uid` int(11) NOT NULL auto_increment,
  `uname` varchar(12) NOT NULL,
  `firstname` varchar(40) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `mail` varchar(40) NOT NULL,
  `birthyear` int(11) NOT NULL default '0',
  `adress` varchar(40) NOT NULL,
  `phone` varchar(12) NOT NULL,
  `extra` varchar(50) default NULL,
  `pass` varchar(20) default NULL,
  `private` varchar(20) default NULL,
  `css` varchar(50) default NULL,
  PRIMARY KEY  (`uid`)
) TYPE=InnoDB;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (0,'','','','',0,'','','','','',''),(1,'kristian','Kristian','Lyngstøl','kristianlyng@gmail.com',1983,'Langretta 10','99014497','Admin- og teknisk crew','hei','ubag',''),(2,'kristian2','Kristoffer','asfyngstol','kristianlyng@gmail.com',1983,'Langretta 10','99014497','Admin/crew/whatever',NULL,'ufl',NULL),(3,'jesus','Jesus','Kristus','gud@himmelen.no',1909,'Himmelporten 2','99014497','Moses er teit','moses','fl',NULL),(4,'wiz','Jørgen','Eriksson Midtbø','jorgenem@gmail.com',1950,'Guristuveien 67','90621199','Heihei','heihei','map',NULL),(5,'vision','Glenn','Høye','vision@boler.no',1984,'Løvsetdalen 4 A','41568182','Vision','glennroa','',NULL),(6,'knut','Knut','Klut','knut@klutistan.klu',1990,'Knutveien 13','99014497','Jeg er en klut','klut','l',NULL),(7,'ppan','Petter','Pan','kris@kross.cra',0,'phooveien2','99014497','petter...','ppan',NULL,NULL),(8,'arntfrid','Arnt','Frid','arntfrid@bohemians.org',1995,'Arntveien 2','99014497','Botten Anna kan bare gå å legge seg.','arntfrid','mbapg',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-04-26 21:11:20
