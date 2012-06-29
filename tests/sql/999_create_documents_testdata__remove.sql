INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `thesis_year_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_created`, `server_date_modified`, `server_date_published`, `server_date_deleted`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(200,'2011-12-01',2009,'Baz University','Bar University','2010-11-02',1999,'masterthesis','1','3','deu',1,4,4,'draft','2007-04-30',2008,'Foo Publishing','Timbuktu','2012-01-03T15:06:40+01:00','2012-01-03T15:06:40+01:00','2012-01-03T15:06:40+01:00',NULL,'published','2',1);

INSERT INTO `document_enrichments` (`id`, `document_id`, `key_name`, `value`) VALUES
(2,200,'validtestkey','Köln');

INSERT INTO `document_identifiers` (`id`, `document_id`, `type`, `value`) VALUES
(600,200,'old','123'),
(601,200,'serial','123'),
(602,200,'uuid','123'),
(603,200,'isbn','123'),
(604,200,'urn','123'),
(605,200,'doi','123'),
(606,200,'handle','123'),
(607,200,'url','123'),
(608,200,'issn','123'),
(609,200,'std-doi','123'),
(610,200,'cris-link','123'),
(611,200,'splash-url','123'),
(612,200,'opus3-id','123'),
(613,200,'opac-id','123'),
(614,200,'pmid','123'),
(615,200,'arxiv','123');

INSERT INTO `document_notes` (`id`, `document_id`, `message`, `visibility`) VALUES
(19,200,'Für die Öffentlichkeit','public'),
(20,200,'Für den Admin','private');

INSERT INTO `document_subjects` (`id`, `document_id`, `language`, `type`, `value`, `external_key`) VALUES
(330,200,'deu','swd','Berlin',NULL),
(331,200,'deu','uncontrolled','Palmöl',NULL);

INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(300,200,'main','KOBV','deu'),
(301,200,'main','COLN','eng'),
(302,200,'abstract','Die KOBV-Zentrale in Berlin-Dahlem.','deu'),
(303,200,'abstract','Lorem impsum.','eng'),
(304,200,'parent','Parent Title','deu'),
(305,200,'sub','Service-Zentrale','deu'),
(306,200,'sub','Service Center','eng'),
(307,200,'additional','Kooperativer Biblioheksverbund Berlin-Brandenburg','deu');

INSERT INTO `link_documents_collections` (`document_id`, `collection_id`, `role_id`) VALUES
(200,16007,1),
(200,1029,3),
(200,2930,4),
(200,6719,5),
(200,7871,6),
(200,13944,7);

INSERT INTO `link_documents_dnb_institutes` (`document_id`, `dnb_institute_id`, `role`) VALUES
(200,1,'grantor'),
(200,3,'publisher');

INSERT INTO `link_documents_licences` (`document_id`, `licence_id`) VALUES
(200,4);

INSERT INTO `link_documents_series` (`document_id`, `series_id`, `number`, `doc_sort_order`) VALUES
(200,1,'6/6',7);

INSERT INTO `persons` (`id`, `academic_title`, `date_of_birth`, `email`, `first_name`, `last_name`, `place_of_birth`) VALUES
(300,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(301, NULL,NULL,'doe@example.org','John','Doe',NULL),
(302,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(303,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(304,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(305,'PhD','1970-01-01',NULL,'Jane','Doe','New York'),
(306,'PhD','1970-01-01',NULL,'Jane','Doe','New York');

INSERT INTO `link_persons_documents` (`person_id`, `document_id`, `role`, `sort_order`, `allow_email_contact`) VALUES
(300,200,'advisor',1,0),
(301,200,'author',1,1),
(302,200,'contributor',1,0),
(303,200,'editor',1,0),
(304,200,'referee',1,0),
(305,200,'translator',1,0),
(306,200,'submitter',1,0);

INSERT INTO `document_patents` (`id`, `document_id`, `countries`, `date_granted`, `number`, `year_applied`, `application`) VALUES
(2,200,'DDR','1970-1-1T0:00:00CET','1234',1970,'The foo machine.');