-- phpMyAdmin SQL Dump
-- version 2.11.4-rc1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 04. Juni 2010 um 14:20
-- Server Version: 5.0.45
-- PHP-Version: 5.2.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `opus400`
--

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE IF NOT EXISTS `collections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,

  `number` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `oai_subset` varchar(255) DEFAULT NULL,

  `left_id` int(10) unsigned NOT NULL,
  `right_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `visible` tinyint(1) unsigned NOT NULL,

  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`,`id`),
  UNIQUE KEY `role_id_left` (`role_id`,`left_id`),
  UNIQUE KEY `role_id_right` (`role_id`,`right_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15985;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections_attributes`
--

CREATE TABLE IF NOT EXISTS `collections_attributes` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) default NULL,
  `value` varchar(255) default NULL,
  KEY `id` (`id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `collections_attributes`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections_roles`
--

CREATE TABLE IF NOT EXISTS `collections_roles` (
   `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
   `name` VARCHAR(255) NOT NULL COMMENT 'Name, label or type of the collection role, i.e. a specific classification or conference.' ,
   `oai_name` VARCHAR(255) NOT NULL COMMENT 'Shortname identifying role in oai context.' ,
   `position` INT(11) UNSIGNED NOT NULL COMMENT 'Position of this collection tree (role) in the sorted list of collection roles for browsing and administration.' ,
   `visible` TINYINT(1) UNSIGNED NOT NULL COMMENT 'Deleted collection trees are invisible. (1=visible, 0=invisible).' ,
   `visible_browsing_start`     TINYINT(1) UNSIGNED NOT NULL    COMMENT 'Show tree on browsing start page. (1=yes, 0=no).' ,
   `display_browsing`           VARCHAR(512) NULL               COMMENT 'Comma separated list of collection_contents_x-fields to display in browsing list context.' ,
   `visible_frontdoor`          TINYINT(1) UNSIGNED NOT NULL    COMMENT 'Show tree on frontdoor. (1=yes, 0=no).' ,
   `display_frontdoor`          VARCHAR(512) NULL               COMMENT 'Comma separated list of collection_contents_x-fields to display in frontdoor context.' ,
   `visible_oai`                TINYINT(1) UNSIGNED NOT NULL    COMMENT 'Show tree in oai output. (1=yes, 0=no).' ,
   `display_oai`                VARCHAR(512) NULL               COMMENT 'collection_contents_x-field to display in oai context.' ,
   PRIMARY KEY (`id`) ,
   UNIQUE INDEX `UNIQUE_NAME` (`name` ASC) ,
   UNIQUE INDEX `UNIQUE_OAI_NAME` (`oai_name` ASC) )
 ENGINE = InnoDB
 DEFAULT CHARSET=utf8
 COMMENT = 'Administration table for the individual collection trees.'
 AUTO_INCREMENT=17;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `collections_enrichments`
--

CREATE TABLE collections_enrichments (
   -- Eindeutige ID fuer die Collection und Referenz auf die role_id,
   -- zu der die Collection gehoert.
   id            INT UNSIGNED NOT NULL,
   collection_id INT(10) unsigned NOT NULL,
   key_name      VARCHAR(255),
   value         VARCHAR(255),

   --
   -- Constraints.
   --
   FOREIGN KEY(collection_id)     REFERENCES collections(id),
   PRIMARY KEY(id),
   INDEX(collection_id, key_name)
) ENGINE = InnoDB
CHARACTER SET = 'utf8'
COLLATE = 'utf8_general_ci';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `link_documents_collections`
--

CREATE TABLE IF NOT EXISTS `link_documents_collections` (
  `document_id` int(10) unsigned NOT NULL,
  `collection_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`document_id`,`collection_id`),
  KEY `role_id` (`role_id`,`collection_id`),
  KEY `collection_id` (`collection_id`),
  KEY `document_id` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

