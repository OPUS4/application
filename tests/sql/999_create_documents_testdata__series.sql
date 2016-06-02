SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_year_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(149, NULL, 2012, NULL, NULL, '2012', 'preprint', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2012, '', NULL, '2012-01-23T15:23:42Z', '2012-01-23T15:23:42Z', 'published', NULL, 0);

INSERT INTO `link_documents_series` (`document_id`, `series_id`, `number`, `doc_sort_order`) VALUES
(149,3,'id-3-is-invisible',1),
(149,4,'id-4-is-visible',1);

INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_year_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(301, NULL, 2012, NULL, NULL, '2013', 'preprint', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2012, '', NULL, '2013-01-23T15:23:42Z', '2013-01-23T15:23:42Z', 'unpublished', NULL, 0);

INSERT INTO `link_documents_series` (`document_id`, `series_id`, `number`, `doc_sort_order`) VALUES
(301,6,'6',1);