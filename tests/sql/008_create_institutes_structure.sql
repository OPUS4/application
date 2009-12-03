
--
-- Dumping data for table `collections_roles`
--

LOCK TABLES `collections_roles` WRITE;
/*!40000 ALTER TABLE `collections_roles` DISABLE KEYS */;
INSERT INTO `collections_roles` VALUES (1,'Organisatorische Einheiten','org',1,'none',1, 1, 'Name', 1, 'Name', 1, 'Name');
/*!40000 ALTER TABLE `collections_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_contents_1`
--

CREATE TABLE `collections_contents_1` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `collections_contents_1`
--

LOCK TABLES `collections_contents_1` WRITE;
/*!40000 ALTER TABLE `collections_contents_1` DISABLE KEYS */;
INSERT INTO `collections_contents_1` VALUES (1,NULL);
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
  PRIMARY KEY  (`id`),
  KEY `fk_link_documents_collections_collections_contents_1` (`collections_id`),
  KEY `fk_link_documents_collections_documents_1` (`documents_id`),
  CONSTRAINT `fk_link_documents_collections_collections_contents_1` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_1` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_link_documents_collections_documents_1` FOREIGN KEY (`documents_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


