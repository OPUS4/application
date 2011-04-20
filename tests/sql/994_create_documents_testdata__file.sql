INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(120, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(121, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(122, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0);


INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(260, 120, 'main', 'Dokument mit sehr, sehr langem Dateinamen', 'deu'),
(261, 121, 'main', 'Dokument mit Dateinamen ohne Dateilabel', 'deu'),
(262, 122, 'main', 'Dokument mit nichtsichtbarer Datei', 'deu');


INSERT INTO `document_files` (`id`, `document_id`, `path_name`, `label`, `mime_type`, `language`, `file_size`, `visible_in_frontdoor`, `visible_in_oai`) VALUES
(121, 120, 'Dies_ist_ein_ganz_besonders_langer_Dateiname.pdf', 'Dies_ist_ein_ganz_besonders_langer_Dateiname.pdf', 'application/pdf', 'eng', 72603, 1, 1),
(122, 121, 'Dateiname_ohne_Dateilabel.pdf', NULL, 'application/pdf', 'eng', 72603, 1, 1),
(123, 122, 'Datei_unsichtaber_in_Frontdoor.pdf', 'Datei_unsichtaber_in_Frontdoor.pdf', 'application/pdf', 'eng', 72603, 0, 1);