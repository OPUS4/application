SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(120, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(121, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(122, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(160, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'unpublished', NULL, 0);


INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(260, 120, 'main', 'Dokument mit sehr, sehr langem Dateinamen', 'deu'),
(261, 121, 'main', 'Dokument mit Dateinamen ohne Dateilabel', 'deu'),
(262, 122, 'main', 'Dokument mit nichtsichtbarer Datei', 'deu'),
(450, 160, 'main', 'Dokument mit Datei zum Löschen beim Testen (Selenium)', 'deu');


INSERT INTO `document_files` (`id`, `document_id`, `path_name`, `label`, `mime_type`, `language`, `file_size`, `visible_in_frontdoor`, `visible_in_oai`) VALUES
(121, 120, 'Dies_ist_ein_ganz_besonders_langer_Dateiname.pdf', 'Dies_ist_ein_ganz_besonders_langer_Dateiname.pdf', 'application/pdf', 'eng', 72603, 1, 1),
(122, 121, 'Dateiname_ohne_Dateilabel.pdf', NULL, 'application/pdf', 'eng', 72603, 1, 1),
(123, 122, 'Datei_unsichtaber_in_Frontdoor.pdf', 'Datei_unsichtaber_in_Frontdoor.pdf', 'application/pdf', 'eng', 72603, 0, 1),
(140, 160, 'deleteme.pdf', 'Datei_zum_loeschen.pdf', 'application/pdf', 'eng', 8817, 1, 1);

INSERT INTO `file_hashvalues` (`file_id`, `type`, `value`) VALUES
(122,'md5','1ba50dc8abc619cea3ba39f77c75c0fe'),
(122,'sha512','24bb2209810bacb3f9c05e08a08aec9ead4ac606fdc7c9d6c5fadffcf66f1e56396fdf46424cf52ef916f9e51f8178fb618c787f952d35aaf6d9079bbc9a50ad'),
(123,'md5','1ba50dc8abc619cea3ba39f77c75c0fe'),
(123,'sha512','24bb2209810bacb3f9c05e08a08aec9ead4ac606fdc7c9d6c5fadffcf66f1e56396fdf46424cf52ef916f9e51f8178fb618c787f952d35aaf6d9079bbc9a50ad');
