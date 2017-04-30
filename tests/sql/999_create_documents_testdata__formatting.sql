SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

INSERT INTO `documents` (`id`, `completed_date`, `completed_year`, `contributing_corporation`, `creating_corporation`, `thesis_date_accepted`, `type`, `edition`, `issue`, `language`, `page_first`, `page_last`, `page_number`, `publication_state`, `published_date`, `published_year`, `publisher_name`, `publisher_place`, `server_date_modified`, `server_date_published`, `server_state`, `volume`, `belongs_to_bibliography`) VALUES
(170, NULL, 2013, NULL, NULL, NULL, 'preprint', NULL, NULL, 'deu', NULL, NULL, NULL, 'draft', NULL, 2013, '', NULL, '2013-09-13T12:00:00Z', '2013-09-13T12:00:00Z', 'unpublished', NULL, 0);

INSERT INTO `document_title_abstracts` (`document_id`, `type`, `value`, `language`) VALUES
(170, 'main', 'Dokument mit langen Zusammenfassungen als Beispiele für die Formatierung', 'deu'),
(170, 'abstract', 'Dies ist eine Zusammenfassung mit mehreren Paragraphen.\n\nHier fängt der zweite Paragraph an. Die Zeilenumbrüche müssen bei der Ausgabe erhalten bleiben.\n\nJetzt kommt dann auch schon der dritte Paragraph mit einer etwas längeren Zeile, die korrekt umgebrochen werden muss.', 'deu'),
(170, 'abstract', 'Diese Zusammenfassung enthält eine sehr lange Zeile ohne Leerzeichen. Auch diese muss umgebrochen werden.\n\n123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890', 'eng');

