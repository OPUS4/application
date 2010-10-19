INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_date_unlocking`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(111, NULL, 2035, NULL, NULL, '2010-09-26', 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2008, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', NULL, 'published', NULL, 0);

INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(249, 111, 'main', '<script type="text/javascript">alert(\'title_main\');</script>', 'deu'),
(250, 111, 'abstract', '<script type="text/javascript">alert(\'title_abstract\');</script> test ', 'deu');

INSERT INTO `link_documents_collections` (`document_id`, `collection_id`, `role_id`) VALUES
(111, 16022, 1);
