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
  `collections_language` varchar(3) character set utf8 NOT NULL default 'ger',
  `name` varchar(255) character set utf8 NOT NULL,
  `number` varchar(3) character set utf8 NOT NULL,
  PRIMARY KEY  (`collections_id`,`collections_language`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `collections_contents_1`
--

INSERT INTO `collections_contents_1` (`collections_id`, `collections_language`, `name`, `number`) VALUES
(1, 'ger', 'Informatik, Informationswissenschaft, allgemeine Werke', '000'),
(1, 'eng', 'Computer science, information, and general works', '000'),
(2, 'ger', 'Philosophie und Psychologie', '100'),
(2, 'eng', 'Philosophy and psychology', '100'),
(3, 'ger', 'Religion', '200'),
(3, 'eng', 'Religion', '200'),
(4, 'ger', 'Sozialwissenschaften', '300'),
(4, 'eng', 'Social sciences', '300'),
(5, 'ger', 'Sprache', '400'),
(5, 'eng', 'Languages', '400'),
(6, 'ger', 'Naturwissenschaften und Mathematik', '500'),
(6, 'eng', 'Science and Mathematics', '500'),
(7, 'ger', 'Technik, Medizin, angewandte Wissenschaften', '600'),
(7, 'eng', 'Technology and applied science', '600'),
(8, 'ger', 'Künste und Unterhaltung', '700'),
(8, 'eng', 'Arts and recreation', '700'),
(9, 'ger', 'Literatur', '800'),
(9, 'eng', 'Literature', '800'),
(10, 'ger', 'Geschichte und Geografie', '900'),
(10, 'eng', 'History and geography and biography', '900'),
(11, 'ger', 'Informatik, Wissen, Systeme', '000'),
(11, 'eng', 'Generalities', '000'),
(12, 'ger', 'Bibliografien', '010'),
(12, 'eng', 'Bibliography', '010'),
(13, 'ger', 'Bibliotheks- und Informationswissenschaften', '020'),
(13, 'eng', 'Library & information sciences', '020'),
(14, 'ger', 'Enzyklopädien, Faktenbücher', '030'),
(14, 'eng', 'General encyclopedic works', '030'),
(15, 'ger', 'Zeitschriften, fortlaufende Sammelwerke', '050'),
(15, 'eng', 'General serials & their indexes', '050'),
(16, 'ger', 'Verbände, Organisationen, Museen', '060'),
(16, 'eng', 'General organization & museology', '060'),
(17, 'ger', 'Publizistische Medien, Journalismus, Verlagswesen', '070'),
(17, 'eng', 'News media, journalism, publishing', '070'),
(18, 'ger', 'Allgemeine Sammelwerke, Zitatensammlungen', '080'),
(18, 'eng', 'General collections', '080'),
(19, 'ger', 'Handschriften, seltene Bücher', '090'),
(19, 'eng', 'Manuscripts & rare books', '090'),
(20, 'ger', 'Informatik, Informationswissenschaft, allgemeine Werke', '000'),
(20, 'eng', 'Generalities', '000'),
(21, 'ger', 'Wissen', '001'),
(21, 'eng', 'Knowledge', '001'),
(22, 'ger', 'Das Buch', '002'),
(22, 'eng', 'The book', '002'),
(23, 'ger', 'Systeme', '003'),
(23, 'eng', 'Systems', '003'),
(24, 'ger', 'Datenverarbeitung; Informatik', '004'),
(24, 'eng', 'Data processing and Computer science', '004'),
(25, 'ger', 'Computerprogrammierung, Programme, Daten', '005'),
(25, 'eng', 'Computer programming, programs, data', '005'),
(26, 'ger', 'Spezielle Computerverfahren', '006'),
(26, 'eng', 'Special computer methods', '006'),
(27, 'ger', 'Philosophie', '100'),
(27, 'eng', 'Philosophy & psychology', '100'),
(28, 'ger', 'Metaphysik', '110'),
(28, 'eng', 'Metaphysics', '110'),
(29, 'ger', 'Epistemologie', '120'),
(29, 'eng', 'Epistemology, causation, humankind', '120'),
(30, 'ger', 'Parapsychologie, Okkultismus', '130'),
(30, 'eng', 'Paranormal phenomena', '130'),
(31, 'ger', 'Philosophische Schulen', '140'),
(31, 'eng', 'Specific philosophical schools', '140'),
(32, 'ger', 'Psychologie', '150'),
(32, 'eng', 'Psychology', '150'),
(33, 'ger', 'Logik', '160'),
(33, 'eng', 'Logic', '160'),
(34, 'ger', 'Ethik', '170'),
(34, 'eng', 'Ethics (Moral philosophy)', '170'),
(35, 'ger', 'Antike, mittelalterliche und östliche Philosophie', '180'),
(35, 'eng', 'Ancient, medieval, Oriental philosophy', '180'),
(36, 'ger', 'Neuzeitliche westliche Philosophie', '190'),
(36, 'eng', 'Modern Western philosophy (19th-century, 20th-century)', '190');

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
  `collections_language` varchar(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` int(11) unsigned NOT NULL,
  `link_docs_path_to_root` tinyint(1) unsigned NOT NULL default '0' COMMENT 'If not 0: Every document belonging to a collection C automatically belongs to every collection on the path from C to the root of the collection tree.',
  `visible` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`collections_roles_id`,`collections_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Verwaltungstabelle fuer die einzelnen Collection-Baeume';

--
-- Daten f�r Tabelle `collections_roles`
--

INSERT INTO `collections_roles` (`collections_roles_id`, `collections_language`, `name`, `position`, `link_docs_path_to_root`, `visible`) VALUES
(1, 'eng', 'Dewey Decimal Classification (DDC)', 1, 0, 1),
(1, 'ger', 'Sachgruppen der Dewey Decimal Classification (DDC)', 1, 0, 1);

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
(667, 0, 1, 74, 0),
(668, 1, 2, 35, 1),
(669, 2, 36, 57, 1),
(670, 3, 58, 59, 1),
(671, 4, 60, 61, 1),
(672, 5, 62, 63, 1),
(673, 6, 64, 65, 1),
(674, 7, 66, 67, 1),
(675, 8, 68, 69, 1),
(676, 9, 70, 71, 1),
(677, 10, 72, 73, 1),
(678, 11, 3, 18, 1),
(679, 12, 19, 20, 1),
(680, 13, 21, 22, 1),
(681, 14, 23, 24, 1),
(682, 15, 25, 26, 1),
(683, 16, 27, 28, 1),
(684, 17, 29, 30, 1),
(685, 18, 31, 32, 1),
(686, 19, 33, 34, 1),
(687, 20, 4, 5, 1),
(688, 21, 6, 7, 1),
(689, 22, 8, 9, 1),
(690, 23, 10, 11, 1),
(691, 24, 12, 13, 1),
(692, 25, 14, 15, 1),
(693, 26, 16, 17, 1),
(694, 27, 37, 38, 1),
(695, 28, 39, 40, 1),
(696, 29, 41, 42, 1),
(697, 30, 43, 44, 1),
(698, 31, 45, 46, 1),
(699, 32, 47, 48, 1),
(700, 33, 49, 50, 1),
(701, 34, 51, 52, 1),
(702, 35, 53, 54, 1),
(703, 36, 55, 56, 1);

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