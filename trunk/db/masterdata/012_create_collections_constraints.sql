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
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `collections`
--
ALTER TABLE `collections`
  ADD CONSTRAINT `collections_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `collections_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `collections_enrichments`
--
ALTER TABLE `collections_enrichments`
  ADD CONSTRAINT `collections_enrichments_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `link_documents_collections`
--
ALTER TABLE `link_documents_collections`
  ADD CONSTRAINT `link_documents_collections_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `link_documents_collections_ibfk_2` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `link_documents_collections_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `collections_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `link_documents_collections_ibfk_4` FOREIGN KEY (`role_id`, `collection_id`) REFERENCES `collections` (`role_id`, `id`);