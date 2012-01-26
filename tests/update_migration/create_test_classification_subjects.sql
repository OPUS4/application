INSERT INTO document_subjects VALUES
(10000, 91, 'deu', 'msc', '00-XX', NULL),
(10001, 91, 'deu', 'msc', '00-02', NULL),
(10002, 91, 'deu', 'ddc', '31', NULL),
(10003, 92, 'deu', 'ddc', '31', NULL),
(10004, 92, 'deu', 'ddc', 'foobar', NULL),
(10005, 1, 'deu', 'ddc', '02', NULL),
(10006, 1, 'deu', 'ddc', '31', NULL),
(10007, 93, 'deu', 'ddc', '1', NULL),
(10008, 93, 'deu', 'ddc', '2', NULL);

-- DDC Nummer 1 wird mehrdeutig gesetzt
UPDATE collections SET number = '1' WHERE id = 14;

