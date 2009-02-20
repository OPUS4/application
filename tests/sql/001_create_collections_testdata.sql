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
-- Tabellenstruktur für Tabelle `collections_roles`
--

CREATE TABLE IF NOT EXISTS `collections_roles` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` int(11) unsigned NOT NULL,
  `link_docs_path_to_root` tinyint(1) unsigned NOT NULL default '0' COMMENT 'If not 0: Every document belonging to a collection C automatically belongs to every collection on the path from C to the root of the collection tree.',
  `visible` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Verwaltungstabelle fuer die einzelnen Collection-Baeume';

--
-- Daten für Tabelle `collections_roles`
--

INSERT INTO `collections_roles` (`id`, `name`, `position`, `link_docs_path_to_root`, `visible`) VALUES
(1, 'Dewey Decimal Classification (DDC)', 1, 0, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections_contents_1`
--

CREATE TABLE IF NOT EXISTS `collections_contents_1` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(255) character set utf8 NOT NULL,
  `number` varchar(3) character set utf8 NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `collections_contents_1`
--

INSERT INTO `collections_contents_1` (`id`, `name`, `number`) VALUES
(1, 'Hidden Root Node', ''),
(2, 'Informatik, Informationswissenschaft, allgemeine Werke', '000'),
(3, 'Philosophie und Psychologie', '100'),
(4, 'Religion', '200'),
(5, 'Sozialwissenschaften', '300'),
(6, 'Sprache', '400'),
(7, 'Naturwissenschaften und Mathematik', '500'),
(8, 'Technik, Medizin, angewandte Wissenschaften', '600'),
(9, 'Künste und Unterhaltung', '700'),
(10, 'Literatur', '800'),
(11, 'Geschichte und Geografie', '900'),
(12, 'Informatik, Wissen, Systeme', '000'),
(13, 'Bibliografien', '010'),
(14, 'Bibliotheks- und Informationswissenschaften', '020'),
(15, 'Enzyklopädien, Faktenbücher', '030'),
(16, 'Zeitschriften, fortlaufende Sammelwerke', '050'),
(17, 'Verbände, Organisationen, Museen', '060'),
(18, 'Publizistische Medien, Journalismus, Verlagswesen', '070'),
(19, 'Allgemeine Sammelwerke, Zitatensammlungen', '080'),
(20, 'Handschriften, seltene Bücher', '090'),
(21, 'Informatik, Informationswissenschaft, allgemeine Werke', '000'),
(22, 'Wissen', '001'),
(23, 'Das Buch', '002'),
(24, 'Systeme', '003'),
(25, 'Datenverarbeitung; Informatik', '004'),
(26, 'Computerprogrammierung, Programme, Daten', '005'),
(27, 'Spezielle Computerverfahren', '006'),
(28, 'Philosophie', '100'),
(29, 'Metaphysik', '110'),
(30, 'Epistemologie', '120'),
(31, 'Parapsychologie, Okkultismus', '130'),
(32, 'Philosophische Schulen', '140'),
(33, 'Psychologie', '150'),
(34, 'Logik', '160'),
(35, 'Ethik', '170'),
(36, 'Antike, mittelalterliche und östliche Philosophie', '180'),
(37, 'Neuzeitliche westliche Philosophie', '190');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections_replacement_1`
--

CREATE TABLE IF NOT EXISTS `collections_replacement_1` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `replacement_for_id` int(10) unsigned default NULL,
  `replacement_by_id` int(10) unsigned default NULL,
  `current_replacement_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
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
-- Tabellenstruktur für Tabelle `collections_structure_1`
--

CREATE TABLE IF NOT EXISTS `collections_structure_1` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `left` int(10) unsigned NOT NULL,
  `right` int(10) unsigned NOT NULL,
  `visible` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `fk_collections_structure_collections_contents_1` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=704 ;

--
-- Daten für Tabelle `collections_structure_1`
--

INSERT INTO `collections_structure_1` (`id`, `collections_id`, `left`, `right`, `visible`) VALUES
(1, 1, 1, 74, 0),
(2, 2, 2, 35, 1),
(3, 3, 36, 57, 1),
(4, 4, 58, 59, 1),
(5, 5, 60, 61, 1),
(6, 6, 62, 63, 1),
(7, 7, 64, 65, 1),
(8, 8, 66, 67, 1),
(9, 9, 68, 69, 1),
(10, 10, 70, 71, 1),
(11, 11, 72, 73, 1),
(12, 12, 3, 18, 1),
(13, 13, 19, 20, 1),
(14, 14, 21, 22, 1),
(15, 15, 23, 24, 1),
(16, 16, 25, 26, 1),
(17, 17, 27, 28, 1),
(18, 18, 29, 30, 1),
(19, 19, 31, 32, 1),
(20, 20, 33, 34, 1),
(21, 21, 4, 5, 1),
(22, 22, 6, 7, 1),
(23, 23, 8, 9, 1),
(24, 24, 10, 11, 1),
(25, 25, 12, 13, 1),
(26, 26, 14, 15, 1),
(27, 27, 16, 17, 1),
(28, 28, 37, 38, 1),
(29, 29, 39, 40, 1),
(30, 30, 41, 42, 1),
(31, 31, 43, 44, 1),
(32, 32, 45, 46, 1),
(33, 33, 47, 48, 1),
(34, 34, 49, 50, 1),
(35, 35, 51, 52, 1),
(36, 36, 53, 54, 1),
(37, 37, 55, 56, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `link_documents_collections_1`
--

CREATE TABLE IF NOT EXISTS `link_documents_collections_1` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `collections_id` int(11) unsigned NOT NULL,
  `documents_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `link_documents_collections_1`
--
