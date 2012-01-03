INSERT INTO `documents` VALUES
(146,'2011-12-01',2009,'Baz University','Bar University','2010-11-02',1999,'masterthesis','1','3','deu',1,4,4,'draft','2007-04-30',2008,'Foo Publishing','Timbuktu','2012-01-03T15:06:40+01:00','2012-01-03T15:06:40+01:00','2012-01-03T15:06:40+01:00',NULL,'published','2',0);

INSERT INTO `document_enrichments` VALUES
(1,146,'LegalNotices','Köln');

INSERT INTO `document_identifiers` VALUES
(499,146,'old','123'),
(500,146,'serial','123'),
(501,146,'uuid','123'),
(502,146,'isbn','123'),
(503,146,'urn','123'),
(504,146,'doi','123'),
(505,146,'handle','123'),
(506,146,'url','123'),
(507,146,'issn','123'),
(508,146,'std-doi','123'),
(509,146,'cris-link','123'),
(510,146,'splash-url','123'),
(511,146,'opus3-id','123'),
(512,146,'opac-id','123'),
(513,146,'pmid','123'),
(514,146,'arxiv','123');

INSERT INTO `document_notes` VALUES
(17,146,'Für die Öffentlichkeit','public'),
(18,146,'Für den Admin','private');

INSERT INTO `document_subjects` VALUES
(319,146,'deu','swd','Berlin',NULL),
(320,146,'deu','uncontrolled','Palmöl',NULL);

INSERT INTO `document_title_abstracts` VALUES
(263,146,'main','KOBV','deu'),
(264,146,'main','COLN','eng'),
(265,146,'abstract','Die KOBV-Zentrale in Berlin-Dahlem.','deu'),
(266,146,'abstract','Lorem impsum.','eng'),
(267,146,'parent','Parent Title','deu'),
(268,146,'sub','Service-Zentrale','deu'),
(269,146,'sub','Service Center','eng'),
(270,146,'additional','Kooperativer Biblioheksverbund Berlin-Brandenburg','deu');

INSERT INTO `link_documents_collections` VALUES
(146,63,2),
(146,16007,1),
(146,2,2),
(146,1029,3),
(146,2930,4),
(146,6719,5),
(146,7871,6),
(146,13944,7);

INSERT INTO `link_documents_dnb_institutes` VALUES
(146,1,'grantor'),
(146,2,'publisher');

INSERT INTO `link_documents_licences` VALUES
(146,1);

INSERT INTO `link_documents_series` VALUES
(146,1,'3a');

INSERT INTO `persons` VALUES
(258,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(259,NULL,NULL,'doe@example.org','John','Doe',NULL),
(260,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(261,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(262,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(263,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(264,'PhD','1970-01-01',NULL,'Jane','Doe','New York');

INSERT INTO `link_persons_documents` VALUES
(258,146,'advisor',1,0),
(259,146,'author',1,1),
(260,146,'contributor',1,0),
(261,146,'editor',1,0),
(262,146,'referee',1,0),
(263,146,'translator',1,0),
(264,146,'submitter',1,0);

INSERT INTO `document_patents` VALUES
(1,146,'DDR','1970-1-1T0:00:00CET','1234',1970,'The foo machine.');

INSERT INTO `document_files` VALUES
(126,146,'test.pdf','foo-pdf','foo-pdf file','application/pdf','deu',8817,1,1,NULL);

INSERT INTO `file_hashvalues` VALUES
(126,'md5','1ba50dc8abc619cea3ba39f77c75c0fe'),
(126,'sha512','24bb2209810bacb3f9c05e08a08aec9ead4ac606fdc7c9d6c5fadffcf66f1e56396fdf46424cf52ef916f9e51f8178fb618c787f952d35aaf6d9079bbc9a50ad');

INSERT INTO `access_files` VALUES
(1,126),
(2,126),
(4,126);
