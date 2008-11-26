-- phpMyAdmin SQL Dump
-- version 2.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 21. November 2008 um 09:59
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
  `collections_roles_id` int(11) unsigned NOT NULL,
  `collections_language` varchar(3) character set utf8 NOT NULL,
  `name` varchar(255) character set utf8 NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`collections_roles_id`,`collections_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
