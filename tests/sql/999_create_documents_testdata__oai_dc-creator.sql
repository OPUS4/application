-- 
-- Test fixture for Issue OPUSVIER-2762 and OPUSVIER-3162 (Doc #302)
-- 
-- Oai_IndexControllerTest::testDcCreatorIsAuthorIfExists
-- 
INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `thesis_year_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_created`, `server_date_modified`, `server_date_published`, `server_date_deleted`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
-- 
-- Oai_IndexControllerTest::testDcCreatorIsAuthorIfExists
-- 
(302,NULL,NULL,NULL,'CreatingCorporation',NULL,NULL,'',NULL,NULL,'fra',NULL,NULL,NULL,'draft',NULL,'2013','',NULL,'2013-09-20T12:27:40+02:00','2013-09-20T12:27:40+02:00','2013-09-20T12:27:40+02:00',NULL,'published',NULL,0),
-- 
-- Oai_IndexControllerTest::testDcCreatorIsEditorIfAuthorNotExists
-- 
(303,NULL,NULL,NULL,'CreatingCorporation',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'draft',NULL,'2013','',NULL,'2013-09-20T12:36:08+02:00','2013-09-20T12:36:08+02:00','2013-09-20T12:36:08+02:00',NULL,'published',NULL,0),
-- 
-- Oai_IndexControllerTest::testDcCreatorIsCreatingCorporationIfAuthorAndEditorNotExist
-- 
(304,NULL,NULL,NULL,'CreatingCorporation',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'draft',NULL,'2013','',NULL,'2013-09-20T12:37:49+02:00','2013-09-20T12:37:49+02:00','2013-09-20T12:37:49+02:00',NULL,'published',NULL,0),
-- 
-- Oai_IndexControllerTest::testDcCreatorIsOmittedIfNoValidEntrySupplied
-- 
(305,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'draft',NULL,'2013','',NULL,'2013-09-20T12:39:25+02:00','2013-09-20T12:39:25+02:00','2013-09-20T12:39:25+02:00',NULL,'published',NULL,0);

INSERT INTO `persons` (`id`, `academic_title`, `date_of_birth`, `email`, `first_name`, `last_name`, `place_of_birth`) VALUES
(314,NULL,'1900-01-01',NULL,NULL,'Author','Berlin'),
(315,NULL,'1900-01-01',NULL,NULL,'Editor','Berlin');

INSERT INTO `link_persons_documents` (`person_id`, `document_id`, `role`, `sort_order`, `allow_email_contact`) VALUES
-- 
-- Oai_IndexControllerTest::testDcCreatorIsAuthorIfExists
-- 
(314,302,'author',1,0),
(315,302,'editor',1,0),
-- 
-- Oai_IndexControllerTest::testDcCreatorIsEditorIfAuthorNotExists
-- 
(315,303,'editor',1,0);

INSERT INTO `document_files` (`id`, `document_id`, `path_name`, `label`, `mime_type`, `language`, `file_size`, `visible_in_frontdoor`, `visible_in_oai`, `server_date_submitted`) VALUES
(141, 305, 'server_date_submitted-test.pdf', 'server_date_submitted-test-pdf', 'application/pdf', 'deu', 8817, 1, 1, '2013-12-10' );