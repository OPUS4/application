SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(147, NULL, 2012, NULL, NULL, NULL, 'preprint', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2012, '', NULL, '2012-01-12T11:12:13Z', '2012-01-12T11:12:13Z', 'published', NULL, 0),
(150, NULL, 2012, NULL, NULL, NULL, 'preprint', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2012, '', NULL, '2012-06-21T11:57:13Z', '2012-06-21T11:57:13Z', 'published', NULL, 0),
(152, NULL, 2013, NULL, NULL, NULL, 'preprint', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2012, '', NULL, '2012-06-21T11:57:13Z', '2012-06-21T11:57:13Z', 'published', NULL, 0);


INSERT INTO `document_title_abstracts` (`document_id`, `type`, `value`, `language`) VALUES
(147, 'main', 'Sonderzeichen, die in der Frontdoor korrekt escaped werden müssen, siehe auch Ticket OPUSVIER-1647.', 'deu'),
(150, 'main', 'Autoren mit einem, zwei und drei LaTex-Umlauten, die in den Urls auf der Frontdoor korrekt escaped werden müssen, siehe auch Ticket OPUSVIER-2435.', 'deu'),
(152, 'main', 'Dokumenttitel mit Sonderzeichen %-"-#-&,  vgl. OPUSVIER-2716.', 'deu'),
(305, 'abstract', 'LaTeX $plug in$ test', 'deu'),
(145,'main','OpenAire Test Document','deu');

INSERT INTO `document_files` (`id`, `document_id`, `path_name`, `label`, `mime_type`, `language`, `file_size`, `visible_in_frontdoor`, `visible_in_oai`) VALUES
(130, 147, 'special-chars-%-"-#-&.pdf', 'Dateiname-mit-Sonderzeichen.pdf', 'application/pdf', 'eng', 1, 1, 1 ),
(131, 147, '\'many\'  -  spaces  and  quotes.pdf', 'Dateiname-mit-vielen-Spaces-und-Quotes.pdf', 'application/pdf', 'eng', 1, 1, 1 );

INSERT INTO `access_files` (`role_id`, `file_id`) VALUES
(2,130),
(2,131);

INSERT INTO `persons` (`id`, `academic_title`, `date_of_birth`, `email`, `first_name`, `last_name`, `place_of_birth`) VALUES
(265, NULL, NULL, NULL, 'J\\\"ohn', 'Doe', NULL),
(266, NULL, NULL, NULL, 'J\\\"ane', 'D\\\"oe', NULL),
(267, NULL, NULL, NULL, 'M\\\"ax', 'M\\\"oller-M\\\"uller', NULL);

INSERT INTO `link_persons_documents` (`person_id`, `document_id`, `role`, `sort_order`, `allow_email_contact`) VALUES
(265, 150, 'author', 0, 0),
(266, 150, 'author', 1, 0),
(267, 150, 'referee', 0, 0);
