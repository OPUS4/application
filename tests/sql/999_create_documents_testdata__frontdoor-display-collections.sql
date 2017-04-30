SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_year_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(151, NULL, 2012, NULL, NULL, '2012', 'preprint', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2012, '', NULL, '2012-01-16T19:23:14Z', '2012-01-16T19:23:14Z', 'published', NULL, 0);

INSERT INTO `link_documents_collections` (`document_id`, `collection_id`, `role_id`) VALUES
(151,16204, 18),
(151,16205, 18),
(151,16203, 19);
