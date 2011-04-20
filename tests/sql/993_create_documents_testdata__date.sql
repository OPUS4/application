INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(112, '2011-04-19', 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, NULL, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(113, '2011-04-19', 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', '2010-04-19', NULL, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(114, '2011-04-19', 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(115, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, NULL, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(116, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', '2010-04-19', NULL, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(117, NULL, 2011, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2010, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(118, NULL, 0000, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, NULL, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0),
(119, NULL, 0000, NULL, NULL, NULL, 'article', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 0000, '', NULL, '2010-06-04T02:36:53Z', '2010-03-05T09:47:22Z', 'published', NULL, 0);


INSERT INTO `document_title_abstracts` (`id`, `document_id`, `type`, `value`, `language`) VALUES
(252, 112, 'main', 'Dokument mit (CompletedDate und CompletedYear) ohne (PublishedDate und PublishedYear)', 'deu'),
(253, 113, 'main', 'Dokument mit (CompletedDate und CompletedYear und PubilshedDate) ohne PublishedYear', 'deu'),
(254, 114, 'main', 'Dokument mit (CompletedDate und CompletedYear und PubilshedYear) ohne PublishedDate', 'deu'),
(255, 115, 'main', 'Dokument mit CompletedYear ohne (CompletedDate, PublishedDate und PublishedYear)', 'deu'),
(256, 116, 'main', 'Dokument mit (CompletedYear und PubilshedDate) ohne (CompleteDate und PublishedYear)', 'deu'),
(257, 117, 'main', 'Dokument mit (CompletedYear und PubilshedYear) ohne (CompletedDate und PublishedDate)', 'deu'),
(258, 118, 'main', 'Dokument mit CompletedYear=0000 ohne (CompletedDate, PublishedYear und PublishedDate)', 'deu'),
(259, 119, 'main', 'Dokument mit {CompletedYear,PublishedYear}=0000 ohne (CompletedDate und PublishedDate)', 'deu');

