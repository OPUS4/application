INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `thesis_year_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `article_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_created`, `server_date_modified`, `server_date_published`, `server_date_deleted`, `server_state`, `volume`, `belongs_to_bibliography`, `embargo_date`) VALUES
(146,'2011-12-01',2009,'Baz University','Bar University','2010-11-02',1999,'masterthesis','1','3','deu',1,4,4,2,'draft','2007-04-30',2008,'Foo Publishing','Timbuktu','2012-01-03T15:06:40+01:00','2012-01-03T15:06:40+01:00','2012-01-03T15:06:40+01:00',NULL,'published','2',1,'1984-06-05');

INSERT INTO `document_enrichments` (`id`, `document_id`, `key_name`, `value`) VALUES
(1,146,'validtestkey','Köln'),
(4, 146, 'SourceSwb', 'http://www.test.de'),
(5, 146, 'SourceTitle', 'Dieses Dokument ist auch erschienen als ...'),
(6, 146, 'ClassRvk', 'LI 99660'),
(7, 146, 'ContributorsName', 'John Doe (Foreword) and Jane Doe (Illustration)'),
(8, 146, 'Event', 'Opus4 OAI-Event'),
(9, 146, 'City', 'Opus4 OAI-City'),
(10, 146, 'Country', 'Opus4 OAI-Country'),
(11, 146, 'Relation', 'info:eu-repo/grantAgreement/EC/FP7/12345'),
(12, 145, 'Relation', 'info:eu-repo/grantAgreement/EC/FP7/12345'),
(13, 146, 'Audience', 'Researchers'),
(14, 145, 'Audience', 'Students'),
(15, 146, 'Coverage', 'name=Western Australia; northlimit=-13.5; southlimit=-35.5; westlimit=112.5; eastlimit=129'),
(16, 145, 'Coverage', 'NL');

INSERT INTO `document_identifiers` (`id`, `document_id`, `type`, `value`) VALUES
(499,146,'old','123'),
(500,146,'serial','123'),
(501,146,'uuid','123'),
(502,146,'isbn','123'),
(503,146,'urn','urn:nbn:op:123'),
(504,146,'doi','10.1007/978-3-540-76406-9'),
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

INSERT INTO `document_notes` (`id`, `document_id`, `message`, `visibility`) VALUES
(17,146,'Für die Öffentlichkeit','public'),
(18,146,'Für den Admin','private');

INSERT INTO `document_subjects` (`id`, `document_id`, `language`, `type`, `value`, `external_key`) VALUES
(319,146,'deu','swd','Berlin',NULL),
(320,146,'deu','uncontrolled','Palmöl',NULL);

INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(263,146,'main','KOBV','deu'),
(264,146,'main','COLN','eng'),
(265,146,'abstract','Die KOBV-Zentrale in Berlin-Dahlem.','deu'),
(266,146,'abstract','Lorem impsum.','eng'),
(267,146,'parent','Parent Title','deu'),
(268,146,'sub','Service-Zentrale','deu'),
(269,146,'sub','Service Center','eng'),
(270,146,'additional','Kooperativer Biblioheksverbund Berlin-Brandenburg','deu');

INSERT INTO `link_documents_collections` (`document_id`, `collection_id`, `role_id`) VALUES
(146,16007,1),
(146,2,2),
(146,40,2),
(146,63,2),
(146,494,2),
(146,1029,3),
(146,2930,4),
(146,6719,5),
(146,7871,6),
(146,13944,7),
(145,16216,23),
(146,16216,23);

INSERT INTO `link_documents_dnb_institutes` (`document_id`, `dnb_institute_id`, `role`) VALUES
(146,1,'grantor'),
(146,3,'publisher'),
(146,2,'publisher'),
(146,4,'grantor');

INSERT INTO `link_documents_licences` (`document_id`, `licence_id`) VALUES
(146,4);

INSERT INTO `link_documents_series` (`document_id`, `series_id`, `number`, `doc_sort_order`) VALUES
(146,1,'5/5',6);

INSERT INTO `persons` (`id`, `academic_title`, `date_of_birth`, `email`, `first_name`, `last_name`, `place_of_birth`) VALUES
(258,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(259,NULL,NULL,'doe@example.org','John','Doe',NULL),
(260,'PhD','1970-01-02',NULL,'Jane','Doe', NULL),
(261,'PhD', NULL,NULL,'Jane','Doe','London'),
(262,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(263,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(264,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(270,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(271,NULL,NULL,'doe@example.org','John','Done',NULL);

INSERT INTO `link_persons_documents` (`person_id`, `document_id`, `role`, `sort_order`, `allow_email_contact`) VALUES
(258,146,'advisor',1,0),
(259,146,'author',1,1),
(260,146,'contributor',1,0),
(261,146,'editor',1,0),
(262,146,'referee',1,0),
(263,146,'translator',1,0),
(264,146,'submitter',1,0),
(270,146,'other',1,0),
(271,145,'author',1,0);

INSERT INTO `document_patents` (`id`, `document_id`, `countries`, `date_granted`, `number`, `year_applied`, `application`) VALUES
(1,146,'DDR','1970-1-1T0:00:00CET','1234',1970,'The foo machine.');

INSERT INTO `document_files` (`id`, `document_id`, `path_name`, `label`, `comment`, `mime_type`, `language`, `file_size`, `visible_in_frontdoor`, `visible_in_oai`, `server_date_submitted`, `sort_order`) VALUES
(126,146,'test.pdf','foo-pdf','foo-pdf file','application/pdf','deu',8817,1,1,'2013-12-10', 1);


INSERT INTO `file_hashvalues` (`file_id`, `type`, `value`) VALUES
(126,'md5','1ba50dc8abc619cea3ba39f77c75c0fe'),
(126,'sha512','24bb2209810bacb3f9c05e08a08aec9ead4ac606fdc7c9d6c5fadffcf66f1e56396fdf46424cf52ef916f9e51f8178fb618c787f952d35aaf6d9079bbc9a50ad');

INSERT INTO `access_files` (`role_id`, `file_id`) VALUES
(1,126),
(2,126),
(4,126);
