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
INSERT INTO `content` VALUES (4,16,1,20070314010800,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\npetter','DetteHer',NULL);
INSERT INTO `content` VALUES (4,17,1,20070314010812,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n  \r\n \r\n   \r\n   \r\n \r\n   \r\n\r\n  \r\npetter','DetteHer',NULL);
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
INSERT INTO `content` VALUES (4,18,1,20070314011044,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n* Hva skjer\r\n* Med lister\r\n * Som dette?\r\n\r\n\r\npetter','DetteHer',NULL);
INSERT INTO `content` VALUES (4,19,1,20070314011102,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n* Hva skjer\r\n* Med lister\r\n ** Som dette?\r\n\r\n\r\npetter','DetteHer',NULL);
INSERT INTO `content` VALUES (4,20,1,20070314011125,1,2,'+ !SubWikier\r\nLink til brukere: user:Kristian \r\nLink til news : news:PetterEierDeg\r\n[FrontPage Tilbake til forsiden]\r\n\r\n* Hva skjer\r\n* Med lister\r\n * Som dette?\r\n * Det var\r\n * Litt tamt\r\n\r\n\r\npetter','DetteHer',NULL);
INSERT INTO `content` VALUES (15,1,1,20070314011505,1,2,'* [FrontPage Forsiden]','blanMenu',NULL);
INSERT INTO `content` VALUES (15,2,1,20070314011902,1,2,'* [FrontPage Forsiden]\r\n* Mer info\r\n * Undermenyting','blanMenu',NULL);
INSERT INTO `content` VALUES (15,3,1,20070314012543,1,2,' * [FrontPage Forsiden]\r\n * Mer info\r\n * Undermenyting','blanMenu',NULL);
INSERT INTO `content` VALUES (15,4,1,20070314012955,1,2,'* [FrontPage Forsiden]\r\n* Mer info\r\n* Undermenyting','blanMenu',NULL);
INSERT INTO `content` VALUES (15,5,1,20070314013017,1,2,'* [FrontPage Forsiden]\r\n* Mer info\r\n* Undermenyting\r\n * Dette\r\n * Er\r\n * skjult','blanMenu',NULL);
INSERT INTO `content` VALUES (15,6,1,20070314013753,1,2,'+ BølerLAN\r\n* [FrontPage Forsiden]\r\n* Mer info\r\n* Undermenyting\r\n * Dette\r\n * Er\r\n * skjult','blanMenu',NULL);
INSERT INTO `content` VALUES (1,9,1,20070314013934,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[blanMenu blanMenu er også en meny]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\nkristian / hei\r\n\r\nSlå deg løs!\r\n\r\nHei','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,10,1,20070314014101,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\nkristian / hei\r\n\r\nSlå deg løs!\r\n\r\nHei','BølerLAN',NULL);
INSERT INTO `content` VALUES (15,7,1,20070314014148,1,2,'+ BølerLAN\r\n* [FrontPage Forsiden]\r\n* Mer info\r\n* Undermenyting\r\n * DetteHer\r\n * [HitEn Er]\r\n * skjult','blanMenu',NULL);
INSERT INTO `content` VALUES (16,1,1,20070314014301,1,2,'Du har nå logget ut !\r\n\r\nVelkommen tilbake en annen gang\r\n\r\n[FrontPage Tilbake til forsiden]','Logout',NULL);
INSERT INTO `content` VALUES (15,8,1,20070314014509,1,2,'+ BølerLAN\r\n* [FrontPage Forsiden]\r\n* Undermenyting\r\n * DetteHer\r\n * [HitEn Er]\r\n * skjult','blanMenu',NULL);
INSERT INTO `content` VALUES (15,9,1,20070314014734,1,2,'+ BølerLAN\r\n* [FrontPage Forsiden]\r\n* Undermenyting\r\n * DetteHer\r\n * [HitEn Er]\r\n * skjult\r\n* Game\r\n * Årets compoer\r\n * Tidligere vinnere\r\n * Regler i CS\r\n * Regler i DittenDatten\r\n * KNUT\r\n* Arkiv\r\n * Nyhetsarkiv\r\n * Filarkiv\r\n * Bildearkiv\r\n','blanMenu',NULL);
INSERT INTO `content` VALUES (15,10,1,20070314014954,1,2,'+ BølerLAN\r\n* [FrontPage Forsiden]\r\n* Om BølerLAN\r\n* Ofte stilte spørsmål\r\n* Tid og sted\r\n* Game\r\n * Årets compoer\r\n * Tidligere vinnere\r\n * Regler i CS\r\n * Regler i DittenDatten\r\n * KNUT\r\n* Arkiv\r\n * Nyhetsarkiv\r\n * Filarkiv\r\n * Bildearkiv\r\n* Nettverk\r\n\r\n','blanMenu',NULL);
INSERT INTO `content` VALUES (15,11,1,20070314015009,1,2,'+ BølerLAN\r\n* [FrontPage Forsiden]\r\n* Om BølerLAN\r\n* Ofte stilte spørsmål\r\n* Tid og sted\r\n* Game\r\n * Årets compoer\r\n * Tidligere vinnere\r\n * Regler i CS\r\n * Regler i DittenDatten\r\n\r\n* Arkiv\r\n * Nyhetsarkiv\r\n * Filarkiv\r\n * Bildearkiv\r\n* Nettverk\r\n\r\n','blanMenu',NULL);
INSERT INTO `content` VALUES (15,12,1,20070314015031,1,2,'+ BølerLAN\r\n* [FrontPage Forsiden]\r\n* Om BølerLAN\r\n* Ofte stilte spørsmål\r\n* Tid og sted\r\n* Game\r\n * Årets compoer\r\n * Tidligere vinnere\r\n * Regler i CS\r\n * Regler i DittenDatten\r\n* Arkiv\r\n * Nyhetsarkiv\r\n * Filarkiv\r\n * Bildearkiv\r\n* Nettverk\r\n\r\n','blanMenu',NULL);
INSERT INTO `content` VALUES (1,11,1,20070314020353,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\n|| kristian || hei ||\r\n\r\nSlå deg løs!\r\n\r\nHei','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,12,1,20070314020431,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n\r\n|| kristian || hei ||\r\n\r\nSlå deg løs!\r\n\r\nHei','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,13,1,20070314021359,1,2,'+ BWReg2\r\n\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n|| //Brukernavn// || //Passord// ||\r\n|| kristian || hei ||\r\n\r\nSlå deg løs!\r\n\r\nHei','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,14,1,20070314022040,1,2,'+ BWReg2\r\n[toc]\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n|| //Brukernavn// || //Passord// ||\r\n|| kristian || hei ||\r\n\r\nSlå deg løs!\r\n\r\nHei\r\n\r\n++ Lagring\r\n\r\nAlt her lagres i databasen.\r\n\r\nI tillegg lagres alle revisjoner, med link til hvem som lagde den. Det vil som på en vanlig wiki være mulig å hente fram forskjellene, og muligens revertere endringer. \r\n\r\nDet meste du ser på denne siden er nå generert fra databasen. Selv om det mest synlig av arbeidet er wiki-innholdet, så er det lagt mer arbeid i databasemodellen, rettighetene og HTML-genereringen enn noe annet. ','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,15,1,20070314022114,1,2,'+ BWReg2\r\n[[toc]]\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n|| //Brukernavn// || //Passord// ||\r\n|| kristian || hei ||\r\n\r\nSlå deg løs!\r\n\r\nHei\r\n\r\n++ Lagring\r\n\r\nAlt her lagres i databasen.\r\n\r\nI tillegg lagres alle revisjoner, med link til hvem som lagde den. Det vil som på en vanlig wiki være mulig å hente fram forskjellene, og muligens revertere endringer. \r\n\r\nDet meste du ser på denne siden er nå generert fra databasen. Selv om det mest synlig av arbeidet er wiki-innholdet, så er det lagt mer arbeid i databasemodellen, rettighetene og HTML-genereringen enn noe annet. ','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,16,1,20070314022209,1,2,'+ BWReg2\r\n[[toc Innhold]]\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n|| //Brukernavn// || //Passord// ||\r\n|| kristian || hei ||\r\n\r\nSlå deg løs!\r\n\r\nHei\r\n\r\n++ Lagring\r\n\r\nAlt her lagres i databasen.\r\n\r\nI tillegg lagres alle revisjoner, med link til hvem som lagde den. Det vil som på en vanlig wiki være mulig å hente fram forskjellene, og muligens revertere endringer. \r\n\r\nDet meste du ser på denne siden er nå generert fra databasen. Selv om det mest synlig av arbeidet er wiki-innholdet, så er det lagt mer arbeid i databasemodellen, rettighetene og HTML-genereringen enn noe annet. ','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,17,1,20070314022230,1,2,'+ BWReg2\r\n[[toc]]\r\nBWReg2 benytter Text_Wiki som backend for å presentere innhold. Dette fungerer i skrivende stund.\r\n\r\nCSS koden kan nok forbedres litt med tanke på linjeskift, men det kommer seg.\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n|| //Brukernavn// || //Passord// ||\r\n|| kristian || hei ||\r\n\r\nSlå deg løs!\r\n\r\nHei\r\n\r\n++ Lagring\r\n\r\nAlt her lagres i databasen.\r\n\r\nI tillegg lagres alle revisjoner, med link til hvem som lagde den. Det vil som på en vanlig wiki være mulig å hente fram forskjellene, og muligens revertere endringer. \r\n\r\nDet meste du ser på denne siden er nå generert fra databasen. Selv om det mest synlig av arbeidet er wiki-innholdet, så er det lagt mer arbeid i databasemodellen, rettighetene og HTML-genereringen enn noe annet. ','BølerLAN',NULL);
INSERT INTO `content` VALUES (1,18,1,20070314023124,1,2,'+ BWReg2\r\n[[toc]]\r\n\r\n++ Innhold\r\n\r\nAlt innholdet du ser her, blir parset og rendret av PEAR modulen Text_Wiki som tar seg av å tolke et wiki-språk. Du finner mer informasjon om Text_Wiki sitt wikispråk på deres [http://wiki.ciaweb.net/yawiki/index.php?area=Text_Wiki&page=SamplePage SamplePage].\r\n\r\n++ Eksisterende undersider\r\n[DetteHer Dette er merkelig nok blitt en demonstrasjon av under-wikier]\r\n[HitEn Dette er gudene vet hva for en tilfeldig side.]\r\n[http://glug.grm.hia.no/~kristian/bwreg2demo/template.php?page=blanMenu blanMenu kontrollerer menyen, men blir ikke automatisk tolket som lenke...]\r\n\r\n++ Brukere\r\n\r\nBruk gjerne min bruker intill videre for å teste funksjonalitet:\r\n|| //Brukernavn// || //Passord// ||\r\n|| kristian || hei ||\r\n\r\nI nær fremtid vil vi kunne lagre våre egne brukere :)\r\n\r\n++ Lagring\r\n\r\nAlt her lagres i databasen.\r\n\r\nI tillegg lagres alle revisjoner, med link til hvem som lagde den. Det vil som på en vanlig wiki være mulig å hente fram forskjellene, og muligens revertere endringer. \r\n\r\nDet meste du ser på denne siden er nå generert fra databasen. Selv om det mest synlig av arbeidet er wiki-innholdet, så er det lagt mer arbeid i databasemodellen, rettighetene og HTML-genereringen enn noe annet. \r\n\r\n++ Utseende\r\n\r\nCSS koden som brukes er ganske grov, men funksjonell.\r\n\r\nOm du liker design, bør CSS fila gi deg en viss ide over hva du kan få til. At så mye som mulig er relativt vil bli prioritert. \r\n\r\nLek gjerne med farger, plassering og det meste. Men ha det i bakhodet at dette systemet vil generere ganske innviklet HTML etter hvert, så det er viktig at alt passer inni hverandre.\r\n\r\nDet er kritisk viktig at CSS\'en støtter en eller annen form for \"dropdown\" på nøstede menyer, da dette vil bli brukt i store mengder når vi skal liste større mengder brukere eller av andre grunner har mye informasjon på en side og ikke ønsker å trykke seg ihjel.','BølerLAN',NULL);

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
