INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `thesis_year_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `article_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_created`, `server_date_modified`, `server_date_published`, `server_date_deleted`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(250,'2011-12-01',2009,'Baz University','Bar University','2010-11-02',1999,'masterthesis','1','3','deu',1,4,4,2,'draft','2007-04-30',2008,'Foo Publishing','Timbuktu','2012-01-03T15:06:40+01:00','2012-01-03T15:06:40+01:00','2012-01-03T15:06:40+01:00',NULL,'unpublished','2',1);

INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(310,250,'main','Testdokument fuer die Sortierung von Personen im Metadaten-Formular.','deu');


INSERT INTO `persons` (`id`, `academic_title`, `date_of_birth`, `email`, `first_name`, `last_name`, `place_of_birth`) VALUES
(310,'PhD','1970-01-01',NULL, 'One','Author','New York'),
(311,NULL,'1970-02-01','doe@example.org','Two','Author',NULL),
(312,'PhD','1970-03-01',NULL,'Three','Author','New York'),
(313,'PhD','1970-05-01',NULL,'Jane','Contrib','New York');

INSERT INTO `link_persons_documents` (`person_id`, `document_id`, `role`, `sort_order`, `allow_email_contact`) VALUES
(310,250,'author',1,1),
(311,250,'author',2,0),
(312,250,'author',3,0),
(313,250,'contributor',1,0);
