-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mywebsite
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `invation`
--

DROP TABLE IF EXISTS `invation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invation` (
  `id` int(11) NOT NULL,
  `obrazek` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invation`
--

LOCK TABLES `invation` WRITE;
/*!40000 ALTER TABLE `invation` DISABLE KEYS */;
INSERT INTO `invation` VALUES (1,'invation.png');
/*!40000 ALTER TABLE `invation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pastevents`
--

DROP TABLE IF EXISTS `pastevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pastevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pastevents`
--

LOCK TABLES `pastevents` WRITE;
/*!40000 ALTER TABLE `pastevents` DISABLE KEYS */;
INSERT INTO `pastevents` VALUES (23,'<h2>2022</h2>','<p>Prvn&iacute; a z&aacute;roveÅˆ jubilejn&iacute; des&aacute;t&yacute; roÄn&iacute;k dÅ™&iacute;ve tradiÄn&iacute; akce&nbsp;<strong>SAFE!</strong> Na programu byly rozhovory s hosty, ocenÄ›n&iacute; hr&aacute;ÄÅ¯ a videa ze sez&oacute;ny.</p>\r\n<div class=\"gallery\">&nbsp;</div>',NULL,'[\"images\\/safe01.jpg\",\"images\\/safe02.jpg\",\"images\\/safe03.jpg\",\"images\\/safe04.jpg\"]','2024-11-27 15:37:36'),(44,'<h2>2023</h2>','<p>Posledn&iacute; roÄn&iacute;k akce&nbsp;<strong>SAFE</strong>&nbsp;byl neuvÄ›Å™itelnÄ› nabit&yacute;! Na&scaron;e pozv&aacute;n&iacute; pÅ™ijali n&aacute;sleduj&iacute;c&iacute; host&eacute;:</p>\r\n<ul>\r\n<li><strong>Franti&scaron;ek Bure&scaron;</strong>&nbsp;- statut&aacute;rn&iacute; n&aacute;mÄ›stek prim&aacute;tora a Älen Rady mÄ›sta Kladna</li>\r\n<li><strong>Robert BezdÄ›k</strong>&nbsp;- n&aacute;mÄ›stek prim&aacute;tora a Älen Rady mÄ›sta Kladna</li>\r\n<li><strong>Petr Ditrich</strong>&nbsp;- pÅ™edseda Äesk&eacute; baseballov&eacute; asociace</li>\r\n<li><strong>Gabriel Waage</strong>&nbsp;- pÅ™edseda Äesk&eacute; softballov&eacute; asociace</li>\r\n<li><strong>EvÅ¾enie Vot&iacute;nsk&aacute;</strong>&nbsp;- vedouc&iacute; PR a marketingu Äesk&eacute; baseballov&eacute; asociace</li>\r\n<li><strong>Tom&aacute;&scaron; Duffek</strong>&nbsp;- baseballov&yacute; reprezentant ÄŒesk&eacute; republiky</li>\r\n<li><strong>Martin MuÅ¾&iacute;k</strong>&nbsp;- baseballov&yacute; reprezentant ÄŒesk&eacute; republiky</li>\r\n<li><strong>Filip Proch&aacute;zka</strong>&nbsp;- tren&eacute;r baseballov&eacute; reprezentace ÄŒR U18</li>\r\n<li><strong>Michala KuchaÅ™ov&aacute;</strong>&nbsp;- b&yacute;val&aacute; elitn&iacute; softballov&aacute; hr&aacute;Äka</li>\r\n<li><strong>Nela Jan&aacute;Äkov&aacute;</strong>&nbsp;- softballov&aacute; reprezentantka ÄŒesk&eacute; republiky</li>\r\n</ul>\r\n<p>Tak vz&aacute;cn&iacute; host&eacute; nemohli zÅ¯stat jen tak sedÄ›t, a tak byla uspoÅ™&aacute;d&aacute;na autogrami&aacute;da, kterou dÄ›ti s &uacute;smÄ›vem pÅ™iv&iacute;taly. NechybÄ›lo ani focen&iacute;.</p>',NULL,'[\"images\\/SAFE-225.jpg\",\"images\\/SAFE-380.jpg\",\"images\\/SAFE-396.jpg\",\"images\\/SAFE-125.jpg\"]','2024-11-29 09:04:53');
/*!40000 ALTER TABLE `pastevents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pasteventsen`
--

DROP TABLE IF EXISTS `pasteventsen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pasteventsen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pasteventsen`
--

LOCK TABLES `pasteventsen` WRITE;
/*!40000 ALTER TABLE `pasteventsen` DISABLE KEYS */;
INSERT INTO `pasteventsen` VALUES (6,'<h2>2022</h2>','<p>The first and at the same time the jubilee tenth year of the formerly traditional&nbsp;<strong>SAFE!</strong> The program included Guest interviews, player awards and videos from the season.</p>',NULL,'[\"..\\/..\\/..\\/..\\/images\\/safe01.jpg\",\"..\\/..\\/..\\/..\\/images\\/safe02.jpg\",\"..\\/..\\/..\\/..\\/images\\/safe03.jpg\",\"..\\/..\\/..\\/..\\/images\\/safe04.jpg\"]','2025-03-03 04:58:53'),(7,'<h2>2023</h2>','<p>The last year of the event&nbsp;<strong>SAFE</strong>&nbsp;It was incredibly charged! Our invitation was accepted by the following guests:</p>\r\n<ul>\r\n<li><strong>Franti&scaron;ek Bure&scaron;</strong>&nbsp;- Statutory Deputy Mayor and Member of the Kladno City Council</li>\r\n<li><strong>Robert BezdÄ›k</strong>&nbsp;- Deputy Mayor and Member of the Kladno City Council</li>\r\n<li><strong>Petr Ditrich</strong>&nbsp;- Chairman of the Czech Baseball Association</li>\r\n<li><strong>Gabriel Waage</strong>&nbsp;- Chairman of the Czech Softball Association</li>\r\n<li><strong>EvÅ¾enie Vot&iacute;nsk&aacute;</strong>&nbsp;- Head of PR and Marketing of the Czech Baseball Association</li>\r\n<li><strong>Tom&aacute;&scaron; Duffek</strong>&nbsp;- baseball representative of the Czech Republic</li>\r\n<li><strong>Martin MuÅ¾&iacute;k</strong>&nbsp;- baseball representative of the Czech Republic</li>\r\n<li><strong>Filip Proch&aacute;zka</strong>&nbsp;- Coach of the Czech U18 national baseball team</li>\r\n<li><strong>Michala KuchaÅ™ov&aacute;</strong>&nbsp;- Former elite softball player</li>\r\n<li><strong>Nela Jan&aacute;Äkov&aacute;</strong>&nbsp;- softball representative of the Czech Republic</li>\r\n</ul>\r\n<p>Such distinguished guests could not just sit still, so an autograph session was organized, which the children with They greeted with a smile. There was also a photo shoot.</p>\r\n<div class=\"gallery\">&nbsp;</div>',NULL,'[\"..\\/..\\/..\\/..\\/images\\/SAFE-225.jpg\",\"..\\/..\\/..\\/..\\/images\\/SAFE-380.jpg\",\"..\\/..\\/..\\/..\\/images\\/SAFE-396.jpg\",\"..\\/..\\/..\\/..\\/images\\/SAFE-125.jpg\"]','2025-03-03 05:00:49');
/*!40000 ALTER TABLE `pasteventsen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programme`
--

DROP TABLE IF EXISTS `programme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `programme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programme`
--

LOCK TABLES `programme` WRITE;
/*!40000 ALTER TABLE `programme` DISABLE KEYS */;
INSERT INTO `programme` VALUES (60,'17:30 otevÅ™enÃ­ sÃ¡lu','2024-12-01 19:36:56'),(61,'18:00 slavnostnÃ­ zahÃ¡jenÃ­ SAFE 24 U5 â€“ U9','2024-12-01 19:36:56'),(62,'OcenÄ›nÃ­ hrÃ¡ÄÅ¯ kategoriÃ­ U5 â€“ U9\r\n','2024-12-01 19:36:56'),(63,'VyhlÃ¡Å¡enÃ­ nejlepÅ¡Ã­ch hrÃ¡ÄÅ¯ U11 â€“ muÅ¾i/Å¾eny\r\n','2024-12-01 19:36:56'),(64,'FotografovÃ¡nÃ­ tÃ½mÅ¯/ocenÄ›nÃ½ch','2024-12-01 19:36:56'),(65,'AutogramiÃ¡da hostÅ¯','2024-12-01 19:36:56'),(66,'Medailonky ocenÄ›nÃ½ch hrÃ¡ÄÅ¯','2024-12-01 19:36:56'),(67,'Afterparty','2024-12-01 19:36:56');
/*!40000 ALTER TABLE `programme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmeen`
--

DROP TABLE IF EXISTS `programmeen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `programmeen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmeen`
--

LOCK TABLES `programmeen` WRITE;
/*!40000 ALTER TABLE `programmeen` DISABLE KEYS */;
INSERT INTO `programmeen` VALUES (4,'5:30pm opening of the hall','2025-03-03 03:55:04'),(5,'6pm SAFE 2024 opening ceremony','2025-03-03 03:55:04'),(6,'Award player category U5 â€“ U9','2025-03-03 03:55:04'),(7,'Announcement of the best U11 players â€“  men/women','2025-03-03 03:55:04'),(8,'Team/awardee photography','2025-03-03 03:55:04'),(9,'Guest autograph session','2025-03-03 03:55:04'),(10,'Medallions of awarded players','2025-03-03 03:55:04'),(11,'Afterparty','2025-03-03 03:55:04');
/*!40000 ALTER TABLE `programmeen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_status`
--

DROP TABLE IF EXISTS `reservation_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation_status` (
  `id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_status`
--

LOCK TABLES `reservation_status` WRITE;
/*!40000 ALTER TABLE `reservation_status` DISABLE KEYS */;
INSERT INTO `reservation_status` VALUES (1,0);
/*!40000 ALTER TABLE `reservation_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `termin`
--

DROP TABLE IF EXISTS `termin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `termin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum_cas` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `termin`
--

LOCK TABLES `termin` WRITE;
/*!40000 ALTER TABLE `termin` DISABLE KEYS */;
INSERT INTO `termin` VALUES (1,'2025-06-15 20:02:00');
/*!40000 ALTER TABLE `termin` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-06 15:13:00
