-- MySQL dump 10.15  Distrib 10.0.20-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: pjj_dk
-- ------------------------------------------------------
-- Server version	10.0.20-MariaDB-1~trusty-log

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
-- Table structure for table `uo_chat`
--

DROP TABLE IF EXISTS `uo_chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat` (
  `utime` int(15) NOT NULL DEFAULT '0',
  `ip` bigint(20) DEFAULT NULL,
  `chat` varchar(32) NOT NULL,
  `proxyip` bigint(20) DEFAULT NULL,
  `user_agent` text NOT NULL,
  KEY `chat` (`chat`),
  KEY `ip` (`ip`),
  KEY `utime` (`utime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_adminlog`
--

DROP TABLE IF EXISTS `uo_chat_adminlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_adminlog` (
  `entry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` int(10) unsigned NOT NULL DEFAULT '0',
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `stamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_ip` varchar(15) NOT NULL,
  PRIMARY KEY (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_ban`
--

DROP TABLE IF EXISTS `uo_chat_ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_ban` (
  `chat` char(16) NOT NULL DEFAULT '',
  `ident` char(10) NOT NULL DEFAULT '',
  `utime` int(11) unsigned NOT NULL DEFAULT '0',
  `auth` char(32) NOT NULL DEFAULT '',
  KEY `chat` (`chat`,`ident`,`utime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_boards`
--

DROP TABLE IF EXISTS `uo_chat_boards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_boards` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `flags` tinytext NOT NULL,
  `utime` int(10) unsigned NOT NULL DEFAULT '0',
  `topic` tinytext NOT NULL,
  `username` tinytext NOT NULL,
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `chat` (`chat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_database`
--

DROP TABLE IF EXISTS `uo_chat_database`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_database` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `username` tinytext NOT NULL,
  `password` varchar(32) NOT NULL DEFAULT '',
  `flags` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `faction` int(10) unsigned NOT NULL DEFAULT '0',
  `prefs` tinytext,
  `icq` int(12) unsigned NOT NULL DEFAULT '0',
  `aim` tinytext,
  `ym` tinytext,
  `msn` tinytext,
  `site` text,
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile` longtext NOT NULL,
  `icon` text NOT NULL,
  `chain` text NOT NULL,
  `picon` text NOT NULL,
  `pimage` text NOT NULL,
  `plink` text NOT NULL,
  `pcolor` tinytext NOT NULL,
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0',
  `skype` tinytext,
  `lastfm` tinytext,
  `flickr` tinytext,
  `facebook` tinytext NOT NULL,
  `gplus` tinytext NOT NULL,
  `steam` tinytext NOT NULL,
  `displayname` tinytext,
  `dtime` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `chat` (`chat`),
  KEY `username` (`username`(5)),
  KEY `displayname` (`displayname`(5)),
  KEY `password` (`password`,`email`(220))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_faction`
--

DROP TABLE IF EXISTS `uo_chat_faction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_faction` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `chat` (`chat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_gag`
--

DROP TABLE IF EXISTS `uo_chat_gag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_gag` (
  `chat` char(16) NOT NULL DEFAULT '',
  `ident` char(10) NOT NULL DEFAULT '',
  `utime` int(11) unsigned NOT NULL DEFAULT '0',
  `auth` char(32) NOT NULL DEFAULT '',
  KEY `chat` (`chat`,`ident`,`utime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_ignore`
--

DROP TABLE IF EXISTS `uo_chat_ignore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_ignore` (
  `chat` char(16) NOT NULL DEFAULT '',
  `ident` char(10) NOT NULL,
  `utime` int(11) unsigned NOT NULL DEFAULT '0',
  `auth` char(8) NOT NULL DEFAULT '',
  KEY `chat` (`chat`,`ident`,`utime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_last`
--

DROP TABLE IF EXISTS `uo_chat_last`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_last` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `utime` int(11) unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL DEFAULT '',
  `owner` varchar(64) NOT NULL DEFAULT '',
  `numwarn` int(3) unsigned NOT NULL DEFAULT '0',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0',
  `prefs` varchar(32) NOT NULL DEFAULT '0',
  `regnotes` text,
  `chat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`chat`),
  UNIQUE KEY `chat_id` (`chat_id`),
  KEY `utime` (`utime`),
  KEY `chat` (`chat`,`utime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_lastfm`
--

DROP TABLE IF EXISTS `uo_chat_lastfm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_lastfm` (
  `lastfm_id` int(11) NOT NULL AUTO_INCREMENT,
  `lastfm_username` varchar(250) NOT NULL,
  `lastfm_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastfm_data` text NOT NULL,
  PRIMARY KEY (`lastfm_id`),
  UNIQUE KEY `lastfm_username` (`lastfm_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_log`
--

DROP TABLE IF EXISTS `uo_chat_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_log` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `ident` varchar(9) NOT NULL DEFAULT '',
  `line` text NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `proxyip` varchar(250) DEFAULT NULL,
  `ip_n` bigint(20) DEFAULT NULL,
  `posttime` datetime DEFAULT NULL,
  `rawpost` text NOT NULL,
  `xmlpost` text NOT NULL,
  `color` tinytext NOT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  KEY `chat` (`chat`),
  KEY `ip` (`ip`),
  KEY `ident` (`ident`),
  KEY `ip_n` (`ip_n`),
  KEY `posttime` (`posttime`),
  KEY `chat_2` (`chat`,`posttime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_log2`
--

DROP TABLE IF EXISTS `uo_chat_log2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_log2` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `ident` varchar(8) NOT NULL DEFAULT '',
  `line` text NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `proxyip` varchar(250) DEFAULT NULL,
  `ip_n` bigint(20) DEFAULT NULL,
  `posttime` datetime DEFAULT NULL,
  `rawpost` text NOT NULL,
  `xmlpost` text NOT NULL,
  `color` tinytext NOT NULL,
  `uid` int(10) unsigned DEFAULT NULL,
  KEY `chat` (`chat`),
  KEY `ip` (`ip`),
  KEY `ident` (`ident`),
  KEY `ip_n` (`ip_n`),
  KEY `posttime` (`posttime`),
  KEY `chat_2` (`chat`,`posttime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_message`
--

DROP TABLE IF EXISTS `uo_chat_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_message` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chat` tinytext,
  `username` tinytext,
  `msg` text NOT NULL,
  `auth` tinytext,
  `archived` enum('yes','no') NOT NULL DEFAULT 'no',
  `unread` enum('yes','no') NOT NULL DEFAULT 'no',
  `rcpt_uid` int(10) unsigned DEFAULT NULL,
  `auth_uid` int(10) unsigned DEFAULT NULL,
  `msg_stamp` datetime NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `rcpt_uid` (`rcpt_uid`),
  KEY `auth_uid` (`auth_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_newpass`
--

DROP TABLE IF EXISTS `uo_chat_newpass`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_newpass` (
  `pass_uid` int(10) unsigned NOT NULL,
  `pass_key` char(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `pass_stamp` datetime NOT NULL,
  PRIMARY KEY (`pass_uid`,`pass_key`),
  CONSTRAINT `uo_chat_newpass_ibfk_1` FOREIGN KEY (`pass_uid`) REFERENCES `uo_chat_database` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_icelandic_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_poll`
--

DROP TABLE IF EXISTS `uo_chat_poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_poll` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `topic` text NOT NULL,
  `nselect` tinyint(4) NOT NULL DEFAULT '0',
  `ta` text NOT NULL,
  `ca` varchar(6) NOT NULL DEFAULT '',
  `tb` text NOT NULL,
  `cb` varchar(6) NOT NULL DEFAULT '',
  `tc` text NOT NULL,
  `cc` varchar(6) NOT NULL DEFAULT '',
  `td` text NOT NULL,
  `cd` varchar(6) NOT NULL DEFAULT '',
  `te` text NOT NULL,
  `ce` varchar(6) NOT NULL DEFAULT '',
  PRIMARY KEY (`chat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_regapps`
--

DROP TABLE IF EXISTS `uo_chat_regapps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_regapps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chat` varchar(16) CHARACTER SET ascii NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `faction` mediumint(9) NOT NULL DEFAULT '0',
  `description` text,
  `rtime` int(11) unsigned NOT NULL DEFAULT '0',
  `appstat` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `chat` (`chat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_stats`
--

DROP TABLE IF EXISTS `uo_chat_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_stats` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `h00` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h01` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h02` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h03` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h04` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h05` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h06` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h07` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h08` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h09` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h10` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h11` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h12` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h13` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h14` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h15` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h16` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h17` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h18` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h19` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h20` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h21` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h22` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `h23` mediumint(8) unsigned NOT NULL DEFAULT '0',
  KEY `chat` (`chat`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_threads`
--

DROP TABLE IF EXISTS `uo_chat_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_threads` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `utime` int(10) unsigned NOT NULL DEFAULT '0',
  `topic` tinytext NOT NULL,
  `post` text NOT NULL,
  `username` tinytext NOT NULL,
  `dtime` datetime DEFAULT NULL,
  `post_org` text NOT NULL,
  PRIMARY KEY (`id`,`utime`),
  KEY `chat` (`chat`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_ulist`
--

DROP TABLE IF EXISTS `uo_chat_ulist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_ulist` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `ident` varchar(10) NOT NULL DEFAULT '',
  `username` tinytext NOT NULL,
  `link` tinytext NOT NULL,
  `image` tinytext NOT NULL,
  `utime` int(11) unsigned NOT NULL DEFAULT '0',
  KEY `chat` (`chat`),
  KEY `username` (`username`(2)),
  KEY `utime` (`utime`),
  KEY `utime_2` (`utime`,`chat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uo_chat_vote`
--

DROP TABLE IF EXISTS `uo_chat_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uo_chat_vote` (
  `chat` varchar(16) NOT NULL DEFAULT '',
  `utime` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `vote` tinyint(3) NOT NULL DEFAULT '0',
  `valid` tinyint(3) NOT NULL DEFAULT '0',
  KEY `chat` (`chat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-07-21 20:48:05
