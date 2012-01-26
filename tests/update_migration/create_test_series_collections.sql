SET foreign_key_checks = 0;

DELETE FROM collections WHERE role_id = 10;

DELETE FROM collections_roles WHERE id = 10;

DELETE FROM link_documents_collections WHERE role_id = 10;

INSERT INTO collections_roles VALUES (18, 'series', 'series', 13, 1, 1, 'Name', 1, 'Name', 1);

INSERT INTO collections VALUES
(16149, 18, NULL, NULL, NULL, 0, 1, 6, NULL, 1),
(16150, 18, 'testVisible', 'testVisible', 'testVisible', 0, 2, 3, 16149, 1),
(16151, 18, 'testUnvisible', 'testUnvisible', 'testVisible', 0, 4, 5, 16149, 0);

INSERT INTO link_documents_collections VALUES
(90, 16151, 18),
(91, 16150, 18),
(92, 16151, 18),
(93, 16151, 18),
(94, 16151, 18),
(95, 16151, 18),
(96, 16151, 18),
(96, 16150, 18),
(93, 16150, 18),
(95, 16150, 18),
(92, 16150, 18);

INSERT INTO document_identifiers VALUES
(517, 91, 'serial', '1'),
(519, 92, 'serial', '1'),
(520, 94, 'serial', '5'),
(521, 94, 'serial', '6'),
(522, 94, 'serial', '7'),
(523, 95, 'serial', '1'),
(524, 90, 'serial', '2'),
(525, 96, 'serial', '2'),
(526, 96, 'serial', '3');

SET foreign_key_checks = 1;

