
--
-- Dumping data for table `collections_roles`
--

LOCK TABLES `collections_roles` WRITE;
/*!40000 ALTER TABLE `collections_roles` DISABLE KEYS */;
INSERT INTO `collections_roles` VALUES (8,'American Psychological Association (APA) Klassifikation',8,'count',1,'Number Name',NULL,NULL,NULL);
/*!40000 ALTER TABLE `collections_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_contents_8`
--

CREATE TABLE `collections_contents_8` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `number` varchar(8) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `collections_contents_8`
--

LOCK TABLES `collections_contents_8` WRITE;
/*!40000 ALTER TABLE `collections_contents_8` DISABLE KEYS */;
INSERT INTO `collections_contents_8` VALUES (1,NULL,NULL),(2,'Allgemeines','2100'),(3,'Geschichte und theoretische Systeme','2140'),(4,'Psychometrie, Statistik, Methodik','2200'),(5,'Tests und Testen','2220'),(6,'Sensorisches und motorisches Testen','2221'),(7,'Entwicklungstests','2222'),(8,'Persönlichkeitstests','2223'),(9,'Klinische Psychodiagnostik','2224'),(10,'Neuropsychologische Diagnostik','2225'),(11,'Gesundheitspsychologische Tests','2226'),(12,'Pädagogische Messung und Beurteilung','2227'),(13,'Berufs- und arbeitspsychologische Tests','2228'),(14,'Marktpsychologische Tests','2229'),(15,'Statistik und Mathematik','2240'),(16,'Forschungsmethoden und Versuchsplanung','2260'),(17,'Allgemeine Psychologie','2300'),(18,'Wahrnehmung','2320'),(19,'Visuelle Wahrnehmung','2323'),(20,'Auditive Wahrnehmung und Sprachwahrnehmung','2326'),(21,'Motorik','2330'),(22,'Kognitive Prozesse','2340'),(23,'Lernen und Gedächtnis','2343'),(24,'Aufmerksamkeit','2346'),(25,'Motivation und Emotion','2360'),(26,'Bewusstseinszustände','2380'),(27,'Parapsychologie','2390'),(28,'Tierpsychologie und vergleichende Psychologie','2400'),(29,'Lernen und Motivation','2420'),(30,'Sozial- und Instinktverhalten','2440'),(31,'Physiologische Psychologie und Neurowissenschaften','2500'),(32,'Genetik','2510'),(33,'Neuropsychologie und Neurologie','2520'),(34,'Elektrophysiologie','2530'),(35,'Physiologische Prozesse','2540'),(36,'Psychophysiologie','2560'),(37,'Psychopharmakologie','2580'),(38,'Psychologie und Geisteswissenschaften','2600'),(39,'Kunst und Literatur','2610'),(40,'Philosophie und Wissenschaftstheorie','2630'),(41,'Kommunikationssysteme','2700'),(42,'Sprache und Sprechen','2720'),(43,'Massenmedien','2750'),(44,'Entwicklungspsychologie','2800'),(45,'Kognitive Entwicklung und Wahrnehmungsentwicklung','2820'),(46,'Psychosoziale Entwicklung und Persönlichkeitsentwicklung','2840'),(47,'Gerontologie','2860'),(48,'Gesellschaftliche Fragen','2900'),(49,'Soziale Strukturen','2910'),(50,'Religion','2920'),(51,'Kultur und Ethnologie','2930'),(52,'Ehe und Familie','2950'),(53,'Scheidung und Wiederverheiratung','2953'),(54,'Kindererziehung','2956'),(55,'Politik','2960'),(56,'Geschlechtsrollen und Frauenfragen','2970'),(57,'Sexualverhalten und sexuelle Orientierung','2980'),(58,'Alkohol- und Drogenkonsum','2990'),(59,'Sozialpsychologie','3000'),(60,'Gruppendynamik und interpersonelle Prozesse','3020'),(61,'Soziale Wahrnehmung und soziale Kognition','3040'),(62,'Persönlichkeitspsychologie','3100'),(63,'Persönlichkeitseigenschaften und Persönlichkeitsprozesse','3120'),(64,'Persönlichkeitstheorie','3140'),(65,'Psychoanalytische Theorie','3143'),(66,'Psychische und physische Störungen','3200'),(67,'Psychische Störungen','3210'),(68,'Affektive Störungen','3211'),(69,'Schizophrenie und psychotische Zustände','3213'),(70,'Neurosen und Angststörungen','3215'),(71,'Persönlichkeitsstörungen','3217'),(72,'Verhaltensstörungen, antisoziale und selbstdestruktive','3230'),(73,'Sucht','3233'),(74,'Kriminelles Verhalten','3236'),(75,'Entwicklungsstörungen und Autismus','3250'),(76,'Lernstörungen','3253'),(77,'Geistige Behinderung','3256'),(78,'Essstörungen','3260'),(79,'Sprachstörungen','3270'),(80,'Umweltbelastung und Krankheit','3280'),(81,'Physische und psychosomatische Störungen','3290'),(82,'Immunologische Störungen','3291'),(83,'Krebs','3293'),(84,'Herz-Kreislauf-Erkrankungen','3295'),(85,'Neurologische Störungen und Hirnschädigung','3297'),(86,'Sensorische Störungen','3299'),(87,'Behandlung und Prävention','3300'),(88,'Psychotherapie und psychotherapeutische Beratung','3310'),(89,'Kognitive Therapie','3311'),(90,'Verhaltenstherapie und Verhaltensmodifikation','3312'),(91,'Gruppen-, Familien- und Partnertherapie','3313'),(92,'Klientenzentrierte und humanistische Therapie','3314'),(93,'Psychoanalytische Therapie','3315'),(94,'Klinische Psychopharmakologie','3340'),(95,'Spezielle Interventionen','3350'),(96,'Klinische Hypnose','3351'),(97,'Selbsthilfegruppen','3353'),(98,'Laienhilfe, paraprofessionelle Beratung und Seelsorge','3355'),(99,'Kunst-, Musik- und Bewegungstherapie','3357'),(100,'Gesundheitspsychologie und Medizin','3360'),(101,'Verhaltensmedizinische und psychologische Behandlung','3361'),(102,'Medizinische Behandlung','3363'),(103,'Gesundheitsförderung und Vorsorge','3365'),(104,'Psychosoziale Dienste und Gesundheitsversorgung','3370'),(105,'Ambulante Dienste','3371'),(106,'Gemeindenahe und soziale Dienste','3373'),(107,'Häusliche Pflege und Hospizbetreuung','3375'),(108,'Pflegeheime und Heimerziehung','3377'),(109,'Stationäre Behandlung','3379'),(110,'Rehabilitation','3380'),(111,'Drogen- und Alkoholrehabilitation','3383'),(112,'Berufliche Rehabilitation','3384'),(113,'Sprachtherapie','3385'),(114,'Strafvollzug und Resozialisierung','3386'),(115,'Berufliche Fragen in Psychologie und Gesundheitswesen','3400'),(116,'Ausbildung und Fortbildung','3410'),(117,'Professionelle Einstellungen und Personmerkmale','3430'),(118,'Berufliche Ethik und berufliche Standards','3450'),(119,'Berufsbeeinträchtigende Störungen','3470'),(120,'Pädagogische Psychologie','3500'),(121,'Bildungsorganisation und pädagogisches Personal','3510'),(122,'Curricula, Bildungsprogramme und Lehrmethoden','3530'),(123,'Lernen und Leistung','3550'),(124,'Interaktion, Anpassung und Einstellungen','3560'),(125,'Sonderpädagogik und Förderunterricht','3570'),(126,'Hochbegabtenpädagogik','3575'),(127,'Schul- und Bildungsberatung','3580'),(128,'Arbeits- und Organisationspsychologie','3600'),(129,'Berufliche Interessen, berufliche Laufbahn und Berufsberatung','3610'),(130,'Personalmanagement, Personalauslese und Personalausbildung','3620'),(131,'Personalbewertung und Arbeitsleistung','3630'),(132,'Management und Managementtraining','3640'),(133,'Arbeitnehmereinstellungen und Arbeitszufriedenheit','3650'),(134,'Organisationsverhalten','3660'),(135,'Arbeitsbedingungen und Arbeitssicherheit','3670'),(136,'Sportpsychologie und Freizeit','3700'),(137,'Sport','3720'),(138,'Freizeit und Erholung','3740'),(139,'Militärpsychologie','3800'),(140,'Marktpsychologie','3900'),(141,'Konsumenteneinstellungen und Konsumentenverhalten','3920'),(142,'Marketing und Werbung','3940'),(143,'Umwelt und Umweltgestaltung','4000'),(144,'Ergonomie','4010'),(145,'Raumgestaltung','4030'),(146,'Stadt- und Umweltplanung','4050'),(147,'Umweltprobleme und Umwelteinstellungen','4070'),(148,'Verkehr','4090'),(149,'Intelligente Systeme','4100'),(150,'Künstliche Intelligenz und Expertensysteme','4120'),(151,'Robotik','4140'),(152,'Neuronale Netzwerke','4160'),(153,'Rechtspsychologie und Kriminologie','4200'),(154,'Zivil- und Menschenrechte','4210'),(155,'Strafrecht und Strafverfolgung','4230'),(156,'Mediation und Konfliktlösung','4250'),(157,'Kriminalprävention','4270'),(158,'Polizei, Strafvollzugs- und Rechtspflegeberufe','4290');
/*!40000 ALTER TABLE `collections_contents_8` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_structure_8`
--

CREATE TABLE `collections_structure_8` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `left` int(10) unsigned NOT NULL,
  `right` int(10) unsigned NOT NULL,
  `visible` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `fk_collections_structure_collections_contents_8` (`collections_id`),
  CONSTRAINT `fk_collections_structure_collections_contents_8` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_8` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `collections_structure_8`
--

LOCK TABLES `collections_structure_8` WRITE;
/*!40000 ALTER TABLE `collections_structure_8` DISABLE KEYS */;
INSERT INTO `collections_structure_8` VALUES (1,1,1,316,0),(2,2,2,5,1),(3,3,3,4,1),(4,4,6,31,1),(5,5,7,26,1),(6,6,8,9,1),(7,7,10,11,1),(8,8,12,13,1),(9,9,14,15,1),(10,10,16,17,1),(11,11,18,19,1),(12,12,20,21,1),(13,13,22,23,1),(14,14,24,25,1),(15,15,27,28,1),(16,16,29,30,1),(17,17,32,53,1),(18,18,33,38,1),(19,19,34,35,1),(20,20,36,37,1),(21,21,39,40,1),(22,22,41,46,1),(23,23,42,43,1),(24,24,44,45,1),(25,25,47,48,1),(26,26,49,50,1),(27,27,51,52,1),(28,28,54,59,1),(29,29,55,56,1),(30,30,57,58,1),(31,31,60,73,1),(32,32,61,62,1),(33,33,63,64,1),(34,34,65,66,1),(35,35,67,68,1),(36,36,69,70,1),(37,37,71,72,1),(38,38,74,79,1),(39,39,75,76,1),(40,40,77,78,1),(41,41,80,85,1),(42,42,81,82,1),(43,43,83,84,1),(44,44,86,93,1),(45,45,87,88,1),(46,46,89,90,1),(47,47,91,92,1),(48,48,94,115,1),(49,49,95,96,1),(50,50,97,98,1),(51,51,99,100,1),(52,52,101,106,1),(53,53,102,103,1),(54,54,104,105,1),(55,55,107,108,1),(56,56,109,110,1),(57,57,111,112,1),(58,58,113,114,1),(59,59,116,121,1),(60,60,117,118,1),(61,61,119,120,1),(62,62,122,129,1),(63,63,123,124,1),(64,64,125,128,1),(65,65,126,127,1),(66,66,130,171,1),(67,67,131,140,1),(68,68,132,133,1),(69,69,134,135,1),(70,70,136,137,1),(71,71,138,139,1),(72,72,141,146,1),(73,73,142,143,1),(74,74,144,145,1),(75,75,147,152,1),(76,76,148,149,1),(77,77,150,151,1),(78,78,153,154,1),(79,79,155,156,1),(80,80,157,158,1),(81,81,159,170,1),(82,82,160,161,1),(83,83,162,163,1),(84,84,164,165,1),(85,85,166,167,1),(86,86,168,169,1),(87,87,172,227,1),(88,88,173,184,1),(89,89,174,175,1),(90,90,176,177,1),(91,91,178,179,1),(92,92,180,181,1),(93,93,182,183,1),(94,94,185,186,1),(95,95,187,196,1),(96,96,188,189,1),(97,97,190,191,1),(98,98,192,193,1),(99,99,194,195,1),(100,100,197,204,1),(101,101,198,199,1),(102,102,200,201,1),(103,103,202,203,1),(104,104,205,216,1),(105,105,206,207,1),(106,106,208,209,1),(107,107,210,211,1),(108,108,212,213,1),(109,109,214,215,1),(110,110,217,226,1),(111,111,218,219,1),(112,112,220,221,1),(113,113,222,223,1),(114,114,224,225,1),(115,115,228,237,1),(116,116,229,230,1),(117,117,231,232,1),(118,118,233,234,1),(119,119,235,236,1),(120,120,238,253,1),(121,121,239,240,1),(122,122,241,242,1),(123,123,243,244,1),(124,124,245,246,1),(125,125,247,250,1),(126,126,248,249,1),(127,127,251,252,1),(128,128,254,269,1),(129,129,255,256,1),(130,130,257,258,1),(131,131,259,260,1),(132,132,261,262,1),(133,133,263,264,1),(134,134,265,266,1),(135,135,267,268,1),(136,136,270,275,1),(137,137,271,272,1),(138,138,273,274,1),(139,139,276,277,1),(140,140,278,283,1),(141,141,279,280,1),(142,142,281,282,1),(143,143,284,295,1),(144,144,285,286,1),(145,145,287,288,1),(146,146,289,290,1),(147,147,291,292,1),(148,148,293,294,1),(149,149,296,303,1),(150,150,297,298,1),(151,151,299,300,1),(152,152,301,302,1),(153,153,304,315,1),(154,154,305,306,1),(155,155,307,308,1),(156,156,309,310,1),(157,157,311,312,1),(158,158,313,314,1);
/*!40000 ALTER TABLE `collections_structure_8` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections_replacement_8`
--

CREATE TABLE `collections_replacement_8` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `collections_id` int(10) unsigned NOT NULL,
  `replacement_for_id` int(10) unsigned default NULL,
  `replacement_by_id` int(10) unsigned default NULL,
  `current_replacement_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_link_collections_8` (`collections_id`),
  KEY `fk_link_collections_replacement_for_8` (`replacement_for_id`),
  KEY `fk_link_collections_replacement_by_8` (`replacement_by_id`),
  KEY `fk_link_collections_current_replacement_8` (`current_replacement_id`),
  CONSTRAINT `fk_link_collections_8` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_8` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_link_collections_replacement_for_8` FOREIGN KEY (`replacement_for_id`) REFERENCES `collections_contents_8` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_link_collections_replacement_by_8` FOREIGN KEY (`replacement_by_id`) REFERENCES `collections_contents_8` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_link_collections_current_replacement_8` FOREIGN KEY (`current_replacement_id`) REFERENCES `collections_contents_8` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `link_documents_collections_8`
--

CREATE TABLE `link_documents_collections_8` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `collections_id` int(11) unsigned NOT NULL,
  `documents_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_link_documents_collections_collections_contents_8` (`collections_id`),
  KEY `fk_link_documents_collections_documents_8` (`documents_id`),
  CONSTRAINT `fk_link_documents_collections_collections_contents_8` FOREIGN KEY (`collections_id`) REFERENCES `collections_contents_8` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_link_documents_collections_documents_8` FOREIGN KEY (`documents_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `link_documents_collections_8`
--

