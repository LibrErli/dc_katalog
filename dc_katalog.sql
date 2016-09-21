-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 21. September 2016 um 09:25
-- Server Version: 5.1.66
-- PHP-Version: 5.3.3-7+squeeze15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `dc_katalog`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `t_cataloguers_history`
--

CREATE TABLE IF NOT EXISTS `t_cataloguers_history` (
  `record_id` int(11) NOT NULL,
  `paraphe` varchar(10) NOT NULL,
  `datum` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Tabellenstruktur für Tabelle `t_files`
--

CREATE TABLE IF NOT EXISTS `t_files` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `upload_time` datetime NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=288 ;


--
-- Tabellenstruktur für Tabelle `t_gnddata`
--

CREATE TABLE IF NOT EXISTS `t_gnddata` (
  `t_gnddata_id` int(11) NOT NULL AUTO_INCREMENT,
  `gnd_id` varchar(15) NOT NULL,
  `val` varchar(255) NOT NULL,
  `kat` varchar(5) NOT NULL,
  PRIMARY KEY (`t_gnddata_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Tabellenstruktur für Tabelle `t_metadata`
--

CREATE TABLE IF NOT EXISTS `t_metadata` (
  `element_id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) NOT NULL,
  `dc_element` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `attribute_type` varchar(125) NOT NULL,
  `attribute_value` varchar(255) NOT NULL,
  PRIMARY KEY (`element_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5954 ;



--
-- Tabellenstruktur für Tabelle `t_record`
--

CREATE TABLE IF NOT EXISTS `t_record` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY (`record_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=253 ;



--
-- Tabellenstruktur für Tabelle `ut_dcschema`
--

CREATE TABLE IF NOT EXISTS `ut_dcschema` (
  `sort_id` int(11) NOT NULL AUTO_INCREMENT,
  `dc_schema` varchar(25) NOT NULL,
  PRIMARY KEY (`sort_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Daten für Tabelle `ut_dcschema`
--

INSERT INTO `ut_dcschema` (`sort_id`, `dc_schema`) VALUES
(1, 'identifier'),
(2, 'language'),
(3, 'creator'),
(4, 'contributor'),
(5, 'title'),
(6, 'date'),
(7, 'description'),
(8, 'publisher'),
(9, 'relation'),
(10, 'rights'),
(11, 'source'),
(12, 'format'),
(13, 'coverage'),
(14, 'subject'),
(15, 'type');
