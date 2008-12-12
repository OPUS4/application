-- phpMyAdmin SQL Dump
-- version 2.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 21. November 2008 um 13:04
-- Server Version: 5.0.45
-- PHP-Version: 5.2.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `opus400`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections_contents_1`
--

CREATE TABLE IF NOT EXISTS `collections_contents_1` (
  `collections_id` int(11) unsigned NOT NULL,
  `name` varchar(255) character set utf8 NOT NULL,
  `number` varchar(3) character set utf8 NOT NULL,
  PRIMARY KEY  (`collections_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `collections_contents_1`
--

INSERT INTO `collections_contents_1` (`collections_id`, `name`, `number`) VALUES
(1, 'Informatik, Informationswissenschaft, allgemeine Werke', '000'),
(2, 'Philosophie und Psychologie', '100'),
(3, 'Religion', '200'),
(4, 'Sozialwissenschaften', '300'),
(5, 'Sprache', '400'),
(6, 'Naturwissenschaften und Mathematik', '500'),
(7, 'Technik, Medizin, angewandte Wissenschaften', '600'),
(8, 'Künste und Unterhaltung', '700'),
(9, 'Literatur', '800'),
(10, 'Geschichte und Geografie', '900'),
(11, 'Informatik, Wissen, Systeme', '000'),
(12, 'Bibliografien', '010'),
(13, 'Bibliotheks- und Informationswissenschaften', '020'),
(14, 'Enzyklopädien, Faktenbücher', '030'),
(15, 'Zeitschriften, fortlaufende Sammelwerke', '050'),
(16, 'Verbände, Organisationen, Museen', '060'),
(17, 'Publizistische Medien, Journalismus, Verlagswesen', '070'),
(18, 'Allgemeine Sammelwerke, Zitatensammlungen', '080'),
(19, 'Handschriften, seltene Bücher', '090'),
(20, 'Informatik, Informationswissenschaft, allgemeine Werke', '000'),
(21, 'Wissen', '001'),
(22, 'Das Buch', '002'),
(23, 'Systeme', '003'),
(24, 'Datenverarbeitung; Informatik', '004'),
(25, 'Computerprogrammierung, Programme, Daten', '005'),
(26, 'Spezielle Computerverfahren', '006'),
(27, 'Philosophie', '100'),
(28, 'Metaphysik', '110'),
(29, 'Epistemologie', '120'),
(30, 'Parapsychologie, Okkultismus', '130'),
(31, 'Philosophische Schulen', '140'),
(32, 'Psychologie', '150'),
(33, 'Logik', '160'),
(34, 'Ethik', '170'),
(35, 'Antike, mittelalterliche und östliche Philosophie', '180'),
(36, 'Neuzeitliche westliche Philosophie', '190');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections_replacement_1`
--

CREATE TABLE IF NOT EXISTS `collections_replacement_1` (
  `collections_replacement_id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `replacement_for_id` int(10) unsigned default NULL,
  `replacement_by_id` int(10) unsigned default NULL,
  `current_replacement_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`collections_replacement_id`),
  KEY `fk_link_collections_1` (`collections_id`),
  KEY `fk_link_collections_replacement_for_1` (`replacement_for_id`),
  KEY `fk_link_collections_replacement_by_1` (`replacement_by_id`),
  KEY `fk_link_collections_current_replacement_1` (`current_replacement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `collections_replacement_1`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur f�r Tabelle `collections_roles`
--

CREATE TABLE IF NOT EXISTS `collections_roles` (
  `collections_roles_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` int(11) unsigned NOT NULL,
  `link_docs_path_to_root` tinyint(1) unsigned NOT NULL default '0' COMMENT 'If not 0: Every document belonging to a collection C automatically belongs to every collection on the path from C to the root of the collection tree.',
  `visible` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`collections_roles_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Verwaltungstabelle fuer die einzelnen Collection-Baeume';

--
-- Daten f�r Tabelle `collections_roles`
--

INSERT INTO `collections_roles` (`collections_roles_id`, `name`, `position`, `link_docs_path_to_root`, `visible`) VALUES
(1, 'Dewey Decimal Classification (DDC)', 1, 0, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections_structure_1`
--

CREATE TABLE IF NOT EXISTS `collections_structure_1` (
  `collections_structure_id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `left` int(10) unsigned NOT NULL,
  `right` int(10) unsigned NOT NULL,
  `visible` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`collections_structure_id`),
  KEY `fk_collections_structure_collections_contents_1` (`collections_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=704 ;

--
-- Daten für Tabelle `collections_structure_1`
--

INSERT INTO `collections_structure_1` (`collections_structure_id`, `collections_id`, `left`, `right`, `visible`) VALUES
(1, 0, 1, 74, 0),
(2, 1, 2, 35, 1),
(3, 2, 36, 57, 1),
(4, 3, 58, 59, 1),
(5, 4, 60, 61, 1),
(6, 5, 62, 63, 1),
(7, 6, 64, 65, 1),
(8, 7, 66, 67, 1),
(9, 8, 68, 69, 1),
(10, 9, 70, 71, 1),
(11, 10, 72, 73, 1),
(12, 11, 3, 18, 1),
(13, 12, 19, 20, 1),
(14, 13, 21, 22, 1),
(15, 14, 23, 24, 1),
(16, 15, 25, 26, 1),
(17, 16, 27, 28, 1),
(18, 17, 29, 30, 1),
(19, 18, 31, 32, 1),
(20, 19, 33, 34, 1),
(21, 20, 4, 5, 1),
(22, 21, 6, 7, 1),
(23, 22, 8, 9, 1),
(24, 23, 10, 11, 1),
(25, 24, 12, 13, 1),
(26, 25, 14, 15, 1),
(27, 26, 16, 17, 1),
(28, 27, 37, 38, 1),
(29, 28, 39, 40, 1),
(30, 29, 41, 42, 1),
(31, 30, 43, 44, 1),
(32, 31, 45, 46, 1),
(33, 32, 47, 48, 1),
(34, 33, 49, 50, 1),
(35, 34, 51, 52, 1),
(36, 35, 53, 54, 1),
(37, 36, 55, 56, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `link_documents_collections_1`
--

CREATE TABLE IF NOT EXISTS `link_documents_collections_1` (
  `link_documents_collections_id` int(11) unsigned NOT NULL auto_increment,
  `collections_id` int(11) unsigned NOT NULL,
  `documents_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`link_documents_collections_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `link_documents_collections_1`
--