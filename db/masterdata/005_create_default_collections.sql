
--
-- Dumping data for table `collections_roles`
--

LOCK TABLES `collections_roles` WRITE;
/*!40000 ALTER TABLE `collections_roles` DISABLE KEYS */;
INSERT INTO `collections_roles` 
VALUES 
(1,'Organisatorische Einheiten','org',1,'none',1, 1, 'Name', 1, 'Name', 1, 'Name'),
(9, 'Collections', 'coll', 9, 'none', 1, 1, 'Name', 1, 'Name', 1, 'Name'),
(10, 'Schriftenreihen', 'series', 10, 'none', 1, 1, 'Name', 1, 'Name', 1, 'Name');
/*!40000 ALTER TABLE `collections_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_contents_1`
--

CREATE TABLE `collections_contents_1` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `address` varchar(255) default NULL,
  `city` varchar(255) default NULL,
  `phone` varchar(30) default NULL,
  `dnb_contact_id` varchar(20) default NULL,
  `is_grantor` tinyint default 0,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `collections_contents_1`
--

LOCK TABLES `collections_contents_1` WRITE;
/*!40000 ALTER TABLE `collections_contents_1` DISABLE KEYS */;
INSERT INTO `collections_contents_1` VALUES (1,NULL, NULL, NULL, NULL, NULL, 0);
/*!40000 ALTER TABLE `collections_contents_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_structure_1`
--

CREATE TABLE `collections_structure_1` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `left` int(10) unsigned NOT NULL,
  `right` int(10) unsigned NOT NULL,
  `visible` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `fk_collections_structure_collections_contents_1` (`collections_id`),
  CONSTRAINT `fk_collections_structure_collections_contents_1` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_1` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `collections_structure_1`
--

LOCK TABLES `collections_structure_1` WRITE;
/*!40000 ALTER TABLE `collections_structure_1` DISABLE KEYS */;
INSERT INTO `collections_structure_1` VALUES (1,1,1,2,0);
/*!40000 ALTER TABLE `collections_structure_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_replacement_1`
--

CREATE TABLE `collections_replacement_1` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `replacement_for_id` int(10) unsigned default NULL,
  `replacement_by_id` int(10) unsigned default NULL,
  `current_replacement_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_link_collections_1` (`collections_id`),
  KEY `fk_link_collections_replacement_for_1` (`replacement_for_id`),
  KEY `fk_link_collections_replacement_by_1` (`replacement_by_id`),
  KEY `fk_link_collections_current_replacement_1` (`current_replacement_id`),
  CONSTRAINT `fk_link_collections_1` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_1` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_link_collections_replacement_for_1` FOREIGN KEY (`replacement_for_id`) REFERENCES `collections_contents_1` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_link_collections_replacement_by_1` FOREIGN KEY (`replacement_by_id`) REFERENCES `collections_contents_1` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_link_collections_current_replacement_1` FOREIGN KEY (`current_replacement_id`) REFERENCES `collections_contents_1` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `link_documents_collections_1`
--

CREATE TABLE `link_documents_collections_1` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `collections_id` int(11) unsigned NOT NULL,
  `documents_id` int(11) unsigned NOT NULL,
  `role` enum('unknown', 'publisher', 'grantor') default 'unknown' NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_link_documents_collections_collections_contents_1` (`collections_id`),
  KEY `fk_link_documents_collections_documents_1` (`documents_id`),
  CONSTRAINT `fk_link_documents_collections_collections_contents_1` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_1` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_link_documents_collections_documents_1` FOREIGN KEY (`documents_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Tabellenstruktur für Tabelle `collections_contents_9`
--

CREATE TABLE IF NOT EXISTS `collections_contents_9` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Tabellenstruktur für Tabelle `collections_contents_10`
--

CREATE TABLE IF NOT EXISTS `collections_contents_10` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=256 ;

LOCK TABLES `collections_contents_9` WRITE;
/*!40000 ALTER TABLE `collections_contents_9` DISABLE KEYS */;
INSERT INTO `collections_contents_9` VALUES (1,NULL);
/*!40000 ALTER TABLE `collections_contents_9` ENABLE KEYS */;
UNLOCK TABLES;
LOCK TABLES `collections_contents_10` WRITE;
/*!40000 ALTER TABLE `collections_contents_10` DISABLE KEYS */;
INSERT INTO `collections_contents_10` VALUES (1,NULL);
/*!40000 ALTER TABLE `collections_contents_10` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Tabellenstruktur für Tabelle `collections_replacement_9`
--

CREATE TABLE IF NOT EXISTS `collections_replacement_9` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `replacement_for_id` int(10) unsigned default NULL,
  `replacement_by_id` int(10) unsigned default NULL,
  `current_replacement_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_link_collections_9` (`collections_id`),
  KEY `fk_link_collections_replacement_for_9` (`replacement_for_id`),
  KEY `fk_link_collections_replacement_by_9` (`replacement_by_id`),
  KEY `fk_link_collections_current_replacement_9` (`current_replacement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur für Tabelle `collections_replacement_10`
--

CREATE TABLE IF NOT EXISTS `collections_replacement_10` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `replacement_for_id` int(10) unsigned default NULL,
  `replacement_by_id` int(10) unsigned default NULL,
  `current_replacement_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_link_collections_10` (`collections_id`),
  KEY `fk_link_collections_replacement_for_10` (`replacement_for_id`),
  KEY `fk_link_collections_replacement_by_10` (`replacement_by_id`),
  KEY `fk_link_collections_current_replacement_10` (`current_replacement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur für Tabelle `collections_structure_9`
--

CREATE TABLE IF NOT EXISTS `collections_structure_9` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `left` int(10) unsigned NOT NULL,
  `right` int(10) unsigned NOT NULL,
  `visible` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `fk_collections_structure_collections_contents_9` (`collections_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `collections_structure_9`
--

LOCK TABLES `collections_structure_9` WRITE;
/*!40000 ALTER TABLE `collections_structure_9` DISABLE KEYS */;
INSERT INTO `collections_structure_9` VALUES (1,1,1,2,0);
/*!40000 ALTER TABLE `collections_structure_9` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Tabellenstruktur für Tabelle `collections_structure_10`
--

CREATE TABLE IF NOT EXISTS `collections_structure_10` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `left` int(10) unsigned NOT NULL,
  `right` int(10) unsigned NOT NULL,
  `visible` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `fk_collections_structure_collections_contents_10` (`collections_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=256 ;

--
-- Dumping data for table `collections_structure_10`
--

LOCK TABLES `collections_structure_10` WRITE;
/*!40000 ALTER TABLE `collections_structure_10` DISABLE KEYS */;
INSERT INTO `collections_structure_10` VALUES (1,1,1,2,0);
/*!40000 ALTER TABLE `collections_structure_10` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Tabellenstruktur für Tabelle `link_documents_collections_9`
--

CREATE TABLE IF NOT EXISTS `link_documents_collections_9` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `collections_id` int(11) unsigned NOT NULL,
  `documents_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_link_documents_collections_collections_contents_9` (`collections_id`),
  KEY `fk_link_documents_collections_documents_9` (`documents_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur für Tabelle `link_documents_collections_10`
--

CREATE TABLE IF NOT EXISTS `link_documents_collections_10` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `collections_id` int(11) unsigned NOT NULL,
  `documents_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_link_documents_collections_collections_contents_10` (`collections_id`),
  KEY `fk_link_documents_collections_documents_10` (`documents_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=249 ;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `collections_replacement_9`
--
ALTER TABLE `collections_replacement_9`
  ADD CONSTRAINT `fk_link_collections_9` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_9` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_link_collections_replacement_for_9` FOREIGN KEY (`replacement_for_id`) REFERENCES `collections_contents_9` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_link_collections_replacement_by_9` FOREIGN KEY (`replacement_by_id`) REFERENCES `collections_contents_9` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_link_collections_current_replacement_9` FOREIGN KEY (`current_replacement_id`) REFERENCES `collections_contents_9` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `collections_replacement_10`
--
ALTER TABLE `collections_replacement_10`
  ADD CONSTRAINT `fk_link_collections_10` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_10` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_link_collections_replacement_for_10` FOREIGN KEY (`replacement_for_id`) REFERENCES `collections_contents_10` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_link_collections_replacement_by_10` FOREIGN KEY (`replacement_by_id`) REFERENCES `collections_contents_10` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_link_collections_current_replacement_10` FOREIGN KEY (`current_replacement_id`) REFERENCES `collections_contents_10` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `collections_structure_9`
--
ALTER TABLE `collections_structure_9`
  ADD CONSTRAINT `fk_collections_structure_collections_contents_9` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_9` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `collections_structure_10`
--
ALTER TABLE `collections_structure_10`
  ADD CONSTRAINT `fk_collections_structure_collections_contents_10` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_10` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `link_documents_collections_9`
--
ALTER TABLE `link_documents_collections_9`
  ADD CONSTRAINT `fk_link_documents_collections_collections_contents_9` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_9` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_link_documents_collections_documents_9` FOREIGN KEY (`documents_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `link_documents_collections_10`
--
ALTER TABLE `link_documents_collections_10`
  ADD CONSTRAINT `fk_link_documents_collections_collections_contents_10` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_10` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_link_documents_collections_documents_10` FOREIGN KEY (`documents_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tabellenstruktur für Tabelle `link_persons_documents`
--

CREATE TABLE IF NOT EXISTS `link_persons_documents` (
  `person_id` int(10) unsigned NOT NULL COMMENT 'Primary key and foreign key to: persons.persons_id.',
  `document_id` int(10) unsigned NOT NULL COMMENT 'Primary key and foreign key to: documents.documents_id.',
  `institute_id` int(10) unsigned default NULL COMMENT 'Foreign key to: institutes_contents.institutes_id.',
  `role` enum('advisor','author','contributor','editor','referee','other','translator') NOT NULL COMMENT 'Role of the person in the actual document-person context.',
  `sort_order` tinyint(3) unsigned NOT NULL COMMENT 'Sort order of the persons related to the document.',
  `allow_email_contact` tinyint(1) NOT NULL default '0' COMMENT 'Is e-mail contact in the actual document-person context allowed? (1=yes, 0=no).',
  PRIMARY KEY  (`person_id`,`document_id`,`role`),
  KEY `fk_link_documents_persons_persons` (`person_id`),
  KEY `fk_link_persons_publications_institutes_contents` (`institute_id`),
  KEY `fk_link_persons_documents_documents` (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relation table (documents, persons, institutes_contents).';

