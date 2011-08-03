INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(124, NULL, 2011, NULL, NULL, '2011-09-26', 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2011, '', NULL, '2011-06-04T02:36:53Z', '2110-03-05T09:47:22Z', 'unpublished', NULL, 0);

INSERT INTO `document_files` (`id`, `document_id`, `path_name`, `label`, `mime_type`, `language`, `file_size`, `visible_in_frontdoor`, `visible_in_oai`) VALUES
(125, 124, 'bar.html', 'bar.html', 'text/html', 'eng', 847, 1, 1);

