SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_year_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(153, NULL, 2012, NULL, NULL, '2012', 'preprint', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2012, '', NULL, '2012-01-23T15:23:42Z', '2012-01-23T15:23:42Z', 'published', NULL, 0);

INSERT INTO `document_title_abstracts` (`document_id`, `type`, `value`, `language`) VALUES
(153, 'main', 'Dokument mit mehreren ISSN, ISBN, URL, URN (siehe auch Ticket OPUSVIER-2738).', 'deu');

INSERT INTO `document_identifiers` (`document_id`, `type`, `value`) VALUES
(153, 'issn', '1234-5678'),
(153, 'issn', '4321-8765'),
(153, 'isbn', '1-2345-678-9'),
(153, 'isbn', '1-5432-876-9'),
(153, 'url', 'http://www.myexampledomain.de/foo'),
(153, 'url', 'http://www.myexampledomain.de/bar'),
(153, 'urn', 'urn:nbn:de:foo:123-bar-456'),
(153, 'urn', 'urn:nbn:de:foo:123-bar-789');
