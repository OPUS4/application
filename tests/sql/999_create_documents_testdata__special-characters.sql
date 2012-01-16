INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(147, NULL, 2012, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2012, '', NULL, '2012-01-12T11:12:13Z', '2012-01-12T11:12:13Z', 'published', NULL, 0);

INSERT INTO `document_title_abstracts` (`document_id`, `type`, `value`, `language`) VALUES
(147, 'main', 'Sonderzeichen, die in der Frontdoor korrekt escaped werden müssen, siehe auch Ticket OPUSVIER-1647.', 'deu');

INSERT INTO `document_files` (`id`, `document_id`, `path_name`, `label`, `mime_type`, `language`, `file_size`, `visible_in_frontdoor`, `visible_in_oai`) VALUES
(130, 147, 'special-chars-%-"-#-&.pdf', 'Dateiname-mit-Sonderzeichen.pdf', 'application/pdf', 'eng', 1, 1, 1),
(131, 147, '\'many\'  -  spaces  and  quotes.pdf', 'Dateiname-mit-vielen-Spaces-und-Quotes.pdf', 'application/pdf', 'eng', 1, 1, 1);

INSERT INTO `access_files` (`role_id`, `file_id`) VALUES
(2,130),
(2,131);
